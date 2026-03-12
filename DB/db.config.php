<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "moja_baza";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Konekcija nije uspela: " . mysqli_connect_error());
}

?>