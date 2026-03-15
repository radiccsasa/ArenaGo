<?php
session_start();
header('Content-Type: application/json');
require_once "../DB/db.config.php";
$method = $_POST['methodName'];

function register() {
    global $conn;

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? 'user';

    if(empty($name) || empty($email) || empty($password)) {
        echo json_encode([
            "status" => "error",
            "message" => "Molimo popunite sva polja"
        ]);
        return;
    }

    if(strlen($password) < 6) {
        echo json_encode([
            "status" => "error",
            "message" => "Lozinka mora imati najmanje 6 karaktera"
        ]);
        return;
    }

    $check_query = "SELECT id FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Email adresa je već registrovana"
        ]);
        return;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (email, password, name, role, status, created_at) 
              VALUES ('$email', '$hashedPassword', '$name', '$role', 'active', NOW())";

    if (mysqli_query($conn, $query)) {
        $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        $user = mysqli_fetch_assoc($result);
        
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
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Greška pri registraciji: " . mysqli_error($conn)
        ]);
    }
}


function login() {
    global $conn;

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if(empty($email) || empty($password)) {
        echo json_encode([
            "status" => "error",
            "message" => "Molimo unesite email i lozinku"
        ]);
        return;
    }

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($result) == 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Korisnik sa ovom email adresom ne postoji"
        ]);
        return;
    }
    
    $user = mysqli_fetch_assoc($result);

    // Provera da li je korisnik blokiran
if($user['status'] == 'blocked') {
    echo json_encode([
        "status" => "error",
        "message" => "Vaš nalog je blokiran zbog neprimerenog ponašanja. Kontaktirajte administratora za više informacija."
    ]);
    return;
}

    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Netočna lozinka"
        ]);
        return;
    }

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


switch($method)
{
    case "register" : register(); break;
    case "login" : login(); break;
}

?>