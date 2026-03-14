<?php
session_start();
header('Content-Type: application/json');
require_once "../DB/db.config.php";


$method = $_POST['methodName'];

function getSports()
{
    global $conn;

    $query = "SELECT id, name FROM sports ORDER BY name ASC";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        echo json_encode([
            'status' => 'error',
            'message' => mysqli_error($conn)
        ]);
        return;
    }

    $sports = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $sports[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $sports
    ]);
}

function addTerm()
{
    global $conn;

    $date = $_POST['date'];
    $time = $_POST['time'];
    $price = $_POST['price'];
    $discount = $_POST['discount'];
    $capacity = $_POST['capacity'];
    $sport = $_POST['sport'];

    $userId = $_SESSION['user']['id'];

    $centerQuery = "SELECT id FROM sports_centers WHERE user_id = $userId";
    $centerResult = mysqli_query($conn, $centerQuery);

    if (!$centerResult || mysqli_num_rows($centerResult) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nemate registrovan sportski centar']);
        return;
    }

    $centerRow = mysqli_fetch_assoc($centerResult);
    $centerId = $centerRow['id'];

    $query = "INSERT INTO terms 
              (center_id, sport_id, date, time, price, action_discount, capacity, created_at)
              VALUES 
              ($centerId, $sport, '$date', '$time', $price, $discount, $capacity, NOW())";

    // Izvrši query
    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Termin uspešno dodat',
            'term_id' => mysqli_insert_id($conn)
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Greška pri dodavanju: ' . mysqli_error($conn)
        ]);
    }
}

switch ($method) {
    case 'getSports':
        getSports();
        break;
    case 'addTerm':
        addTerm();
        break;
}
