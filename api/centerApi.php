<?php
session_start();
header('Content-Type: application/json');
require_once "../DB/db.config.php";

$method = $_POST['methodName'];

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Niste prijavljeni']);
    exit();
}

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID korisnika nije pronađen u sesiji']);
    exit();
}

function checkCenterExists() {
    global $conn;
    
    if(!isset($_SESSION['user'])) {
        echo json_encode(['exists' => false]);
        exit();
    }
    
    $userId = $_SESSION['user']['id'];
    $query = "SELECT id FROM sports_centers WHERE user_id = $userId";
    $result = mysqli_query($conn, $query);
    
    echo json_encode([
        'exists' => mysqli_num_rows($result) > 0
    ]);
}

function getCenterData() {
    global $conn;
    
    if(!isset($_SESSION['user'])) {
        echo json_encode(['status' => 'error', 'message' => 'Niste prijavljeni']);
        exit();
    }
    
    $userId = $_SESSION['user']['id'];
    $query = "SELECT * FROM sports_centers WHERE user_id = $userId";
    $result = mysqli_query($conn, $query);
    
    if($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'status' => 'success',
            'data' => $row
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Centar nije pronađen'
        ]);
    }
}

function updateCenter() {
    global $conn;
    
    if(!isset($_SESSION['user'])) {
        echo json_encode(['status' => 'error', 'message' => 'Niste prijavljeni']);
        exit();
    }
    
    $userId = $_SESSION['user']['id'];
    
    // Prvo proveri da li centar pripada ovom korisniku
    $checkQuery = "SELECT id FROM sports_centers WHERE user_id = $userId";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if(mysqli_num_rows($checkResult) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nemate pravo da ažurirate ovaj centar']);
        exit();
    }
    
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    
    $query = "UPDATE sports_centers SET 
              name = '$name',
              description = '$description',
              location = '$location',
              latitude = $latitude,
              longitude = $longitude
              WHERE user_id = $userId";
    
    if(mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}

function createCenter() {
    global $conn;
    
    if(!isset($_SESSION['user'])){
        echo json_encode(["status" => "error", "message" => "Niste prijavljeni"]);
        exit();
    }
    
    $userId = $_SESSION['user']['id'];
    
    $checkQuery = "SELECT id FROM sports_centers WHERE user_id = $userId";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if(mysqli_num_rows($checkResult) > 0) {
        echo json_encode([
            "status" => "error", 
            "message" => "Već imate registrovan centar"
        ]);
        exit();
    }
    
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    
    $query = "INSERT INTO sports_centers
              (user_id, name, description, location, latitude, longitude, created_at)
              VALUES
              ($userId, '$name', '$description', '$location', $latitude, $longitude, NOW())";
    
    if(mysqli_query($conn, $query)){
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
    }
}

function getStats(){

global $conn;

$result = mysqli_query($conn,"SELECT COUNT(*) as total FROM reservations");

$row = mysqli_fetch_assoc($result);

echo json_encode([
"reservations"=>$row['total']
]);

}

function getReservations() {
    global $conn;
    
    if(!isset($_SESSION['user'])) {
        echo json_encode(['status' => 'error', 'message' => 'Niste prijavljeni']);
        exit();
    }
    
    $userId = $_SESSION['user']['id'];
    $filterStatus = isset($_POST['filter_status']) ? $_POST['filter_status'] : '';
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    
    // Prvo dobavi ID centra za ovog korisnika
    $centerQuery = "SELECT id FROM sports_centers WHERE user_id = $userId";
    $centerResult = mysqli_query($conn, $centerQuery);
    
    if(mysqli_num_rows($centerResult) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nemate registrovan centar']);
        exit();
    }
    
    $centerRow = mysqli_fetch_assoc($centerResult);
    $centerId = $centerRow['id'];
    
    $query = "SELECT 
                r.id,
                r.status,
                r.created_at as reservation_date,
                u.name as user_name,
                u.email as user_email,
                s.name as sport_name,
                t.date,
                t.time,
                t.price,
                t.action_discount as discount,
                t.capacity
              FROM reservations r
              INNER JOIN users u ON r.user_id = u.id
              INNER JOIN terms t ON r.term_id = t.id
              INNER JOIN sports s ON t.sport_id = s.id
              WHERE t.center_id = $centerId";
    
    if (!empty($filterStatus)) {
        $filterStatus = $filterStatus;
        $query .= " AND r.status = '$filterStatus'";
    }
    
    if (!empty($search)) {
        $search = $search;
        $query .= " AND u.name LIKE '%$search%'";
    }
    
    $query .= " ORDER BY r.created_at DESC";
    
    $result = mysqli_query($conn, $query);
    
    $reservations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reservations[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $reservations
    ]);
}

function updateReservationStatus() {
    global $conn;
    
    if(!isset($_SESSION['user'])) {
        echo json_encode(['status' => 'error', 'message' => 'Niste prijavljeni']);
        exit();
    }
    
    $userId = $_SESSION['user']['id'];
    $reservationId = $_POST['reservation_id'];
    $newStatus = $_POST['status'];
    
    if (!in_array($newStatus, ['approved', 'rejected'])) {
        echo json_encode(['status' => 'error', 'message' => 'Neispravan status']);
        exit();
    }
    
    $checkQuery = "SELECT r.id FROM reservations r
                   INNER JOIN terms t ON r.term_id = t.id
                   INNER JOIN sports_centers sc ON t.center_id = sc.id
                   WHERE r.id = $reservationId AND sc.user_id = $userId";
    
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($checkResult) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nemate pravo da menjate ovu rezervaciju']);
        exit();
    }
    
    $updateQuery = "UPDATE reservations SET status = '$newStatus' WHERE id = $reservationId";
    
    if (mysqli_query($conn, $updateQuery)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}

switch($method){

case "createCenter":
createCenter();
break;

case "getStats":
getStats();
break;

case "getCenterData":
    getCenterData();
    break;

case "checkCenterExists":
    checkCenterExists();
    break;
case "updateCenter":
    updateCenter();
    break;

    case 'getReservations':
    getReservations();
    break;
case 'updateReservationStatus':
    updateReservationStatus();
    break;


}
?>