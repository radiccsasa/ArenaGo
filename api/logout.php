<?php
session_start();

$_SESSION = array();

session_destroy();

header("Location: /ArenaGo/pages/index/index.php");
exit();
?>