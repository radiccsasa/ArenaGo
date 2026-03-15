<?php
session_start();
require_once "../../DB/db.config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    http_response_code(403);
    exit();
}

$comment_id = $_POST['comment_id'];

$query = "DELETE FROM comments WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $comment_id);

if($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
}
?>