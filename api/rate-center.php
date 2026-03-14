<?php
session_start();
require_once "../DB/db.config.php";

header('Content-Type: application/json');

// Provera sesije
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'user') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Niste prijavljeni']);
    exit();
}

// Provera POST parametara
if(!isset($_POST['center_id']) || !isset($_POST['rating'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Nedostaju podaci']);
    exit();
}

$user_id = $_SESSION['user']['id'];
$center_id = intval($_POST['center_id']);
$rating = intval($_POST['rating']);

if($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ocena mora biti između 1 i 5']);
    exit();
}

// Prvo proveri da li centar postoji
$check_center = "SELECT id FROM sports_centers WHERE id = ?";
$stmt = $conn->prepare($check_center);
$stmt->bind_param("i", $center_id);
$stmt->execute();
if($stmt->get_result()->num_rows == 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Centar ne postoji']);
    exit();
}

// Provera da li već postoji ocena
$check_query = "SELECT id FROM ratings WHERE center_id = ? AND user_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $center_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    // Ažuriranje postojeće ocene
    $update_query = "UPDATE ratings SET rating = ?, created_at = NOW() WHERE center_id = ? AND user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("iii", $rating, $center_id, $user_id);
} else {
    // Dodavanje nove ocene
    $insert_query = "INSERT INTO ratings (center_id, user_id, rating, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iii", $center_id, $user_id, $rating);
}

if($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Ocena sačuvana']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Greška pri čuvanju ocene: ' . $conn->error]);
}
?>