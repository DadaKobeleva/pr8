<?php
session_start();
include("./settings/connect_datebase.php");
include("./check_session.php");

if(isset($_SESSION['user'])) {
    destroySession($_SESSION['user'], $mysqli);
    unset($_SESSION['user']);
}

session_destroy();
header("Location: login.php");
exit;
?>