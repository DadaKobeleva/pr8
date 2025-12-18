<?php
session_start();
include("../settings/connect_datebase.php");

$login = $_POST['login'];
$password = $_POST['password'];

// ищем пользователя
$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$mysqli->real_escape_string($login)."'");

$id = -1;
$user_data = null;
while($user_read = $query_user->fetch_assoc()) {
    $user_data = $user_read;
}

if($user_data) {
    // Проверка пароль с помощью password_verify
    if(password_verify($password, $user_data['password'])) {
        $id = $user_data['id'];
        $_SESSION['user'] = $id;
    }
}

if($id != -1) {
    echo md5(md5($id));
} else {
    echo "";
}
?>