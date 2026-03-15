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

function getAllCenterTerms()
{
    global $conn;

    if (!isset($_SESSION['user']['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Niste prijavljeni']);
        return;
    }

    $userId = $_SESSION['user']['id'];

    // Prvo dobij ID centra
    $centerResult = mysqli_query($conn, "SELECT id FROM sports_centers WHERE user_id = $userId");
    $centerRow = mysqli_fetch_assoc($centerResult);
    $centerId = $centerRow['id'];

    // Termini sa join za sport
    $query = "SELECT t.*, s.name as sport_name 
              FROM terms t
              LEFT JOIN sports s ON t.sport_id = s.id
              WHERE t.center_id = $centerId 
              ORDER BY t.date DESC, t.time DESC";

    $result = mysqli_query($conn, $query);

    $terms = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $terms[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $terms
    ]);
}

function getTerm()
{
    global $conn;

    if (!isset($_SESSION['user']['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Niste prijavljeni']);
        return;
    }

    $userId = $_SESSION['user']['id'];
    $termId = isset($_POST['term_id']) ? (int)$_POST['term_id'] : 0;

    // Provera da li termin pripada centru ovog korisnika
    $query = "SELECT t.* 
              FROM terms t
              INNER JOIN sports_centers sc ON t.center_id = sc.id
              WHERE t.id = $termId AND sc.user_id = $userId";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Termin nije pronađen']);
        return;
    }

    $term = mysqli_fetch_assoc($result);

    echo json_encode([
        'status' => 'success',
        'data' => $term
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

function updateTerm()
{
    global $conn;

    if (!isset($_SESSION['user']['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Niste prijavljeni']);
        return;
    }

    $userId = (int)$_SESSION['user']['id'];
    $termId = isset($_POST['term_id']) ? (int)$_POST['term_id'] : 0;

    if ($termId == 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID termina nije prosleđen']);
        return;
    }

    // Prvo proveri da li termin pripada centru ovog korisnika
    $checkQuery = "SELECT t.id FROM terms t
                   INNER JOIN sports_centers sc ON t.center_id = sc.id
                   WHERE t.id = $termId AND sc.user_id = $userId";

    $checkResult = mysqli_query($conn, $checkQuery);

    if (!$checkResult || mysqli_num_rows($checkResult) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Nemate pravo da menjate ovaj termin']);
        return;
    }

    // Preuzmi i očisti podatke
    $sport_id = isset($_POST['sport']) ? (int)$_POST['sport'] : 0;
    $date = isset($_POST['date']) ? mysqli_real_escape_string($conn, $_POST['date']) : '';
    $time = isset($_POST['time']) ? mysqli_real_escape_string($conn, $_POST['time']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $discount = isset($_POST['discount']) ? floatval($_POST['discount']) : 0;
    $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;

    // Validacija
    if ($sport_id == 0 || empty($date) || empty($time) || $price <= 0 || $capacity <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Sva polja su obavezna']);
        return;
    }

    // Update query
    $query = "UPDATE terms SET 
              sport_id = $sport_id,
              date = '$date',
              time = '$time',
              price = $price,
              action_discount = $discount,
              capacity = $capacity
              WHERE id = $termId";

    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Termin uspešno ažuriran'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Greška pri ažuriranju: ' . mysqli_error($conn)
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
    case 'updateTerm':
        updateTerm();
        break;
    case 'getAllCenterTerms':
        getAllCenterTerms();
        break;
    case 'getTerm':
        getTerm();
        break;
}
