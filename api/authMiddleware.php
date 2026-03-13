
<?php
session_start();
//  na svakoj zasticenoj stranici samo 
//  require_once "../../api/authMiddleware.php";
//  checkAuth(["centar"]); tu sam stavio da mozemo da dodeljujemo pristup stranicama
function checkAuth($allowedRoles = []){

    if(!isset($_SESSION['user'])){
        header("Location: ../login/login.html");
        exit();
    }

    if(!empty($allowedRoles) && !in_array($_SESSION['user']['role'], $allowedRoles)){
        echo "Nemate pristup";
        exit();
    }
}
?>