<?php
session_start();
header('Content-Type: application/json');
require_once "../DB/db.config.php";
$method = $_POST['methodName'];

function register() {
    global $conn;

    $email = $_POST['email'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $role = $_POST['role'];
    

    // hash passworda
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (email, password, name, role, status) 
              VALUES ('$email', '$hashedPassword', '$name', '$role', 'active')";

    if (mysqli_query($conn, $query)) {

    $_SESSION['user'] = [
        "email" => $email,
        "name" => $name,
        "role" => $role
    ];

    echo json_encode([
        "status" => "success",
        "user" => $_SESSION['user']
    ]);
    }

}

function login() {
    global $conn;

    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

        $_SESSION['user'] = [
            "id" => $user['id'],
            "email" => $user['email'],
            "name" => $user['name'],
            "role" => $user['role']
        ];

        echo json_encode([
            "status" => "success",
            "user" => $_SESSION['user']
        ]);
    }
    else {

            echo json_encode([
                "status" => "error",
                "message" => "Wrong password"
            ]);
            }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }

    
}


switch($method)
{
    case "register" : register(); break;
    case "login" : login(); break;
}

?>