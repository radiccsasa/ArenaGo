<?php
session_start();
header('Content-Type: application/json');
require_once "../DB/db.config.php";

$method = $_POST['methodName'];

function createCenter(){

global $conn;

if(!isset($_SESSION['user'])){
echo json_encode(["status"=>"not_logged"]);
exit();
}

$userId = $_SESSION['user']['id'];

$name = $_POST['name'];
$description = $_POST['description'];
$location = $_POST['location'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];

$query = "INSERT INTO centers
(user_id,name,description,location,latitude,longitude,created_at)
VALUES
('$userId','$name','$description','$location','$latitude','$longitude',NOW())";

if(mysqli_query($conn,$query)){
echo json_encode(["status"=>"success"]);
}else{
echo json_encode(["status"=>"error"]);
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

switch($method){

case "createCenter":
createCenter();
break;

case "getStats":
getStats();
break;

}
?>