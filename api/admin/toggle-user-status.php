<?php
session_start();
require_once "../../DB/db.config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    http_response_code(403);
    exit();
}

$user_id = $_POST['user_id'];
$action = $_POST['action'];
$status = ($action == 'block') ? 'blocked' : 'active';

$query = "UPDATE users SET status = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $status, $user_id);

if($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
}
?>