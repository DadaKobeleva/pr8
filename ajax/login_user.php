<?php
session_start();
include("../settings/connect_datebase.php");

$login = isset($_POST['login']) ? trim($_POST['login']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if(empty($login) || empty($password)) {
    echo "Заполните все поля";
    exit;
}

$safe_login = $mysqli->real_escape_string($login);
$safe_password = $mysqli->real_escape_string($password);

$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='$safe_login' AND `password`= '$safe_password'");
$id = -1;

while($user_read = $query_user->fetch_row()) {
    $id = $user_read[0];
}

if($id != -1) {
    $_SESSION['user'] = $id;
    echo md5(md5($id));
} else {
    echo "Неверный логин или пароль";
}
?>