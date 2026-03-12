<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "arenago";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Konekcija nije uspela: " . mysqli_connect_error());
}

?>