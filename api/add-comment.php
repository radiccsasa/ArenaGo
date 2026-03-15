<?php
session_start();
require_once "../DB/db.config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'user') {
    http_response_code(401);
    echo json_encode(['error' => 'Niste prijavljeni']);
    exit();
}

if(!isset($_POST['center_id']) || !isset($_POST['comment']) || empty(trim($_POST['comment']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Nedostaju podaci']);
    exit();
}

$user_id = $_SESSION['user']['id'];
$center_id = $_POST['center_id'];
$comment = trim($_POST['comment']);

$insert_query = "INSERT INTO comments (center_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("iis", $center_id, $user_id, $comment);

if($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Greška pri čuvanju komentara']);
}
?>