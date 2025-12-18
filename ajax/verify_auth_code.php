<?php
session_start();
include("../settings/connect_datebase.php");
include("../check_session.php");

$code = $_POST['code'];

if(!isset($_SESSION['auth_user_id'])) {
    echo json_encode(["success" => false, "message" => "Сессия истекла"]);
    exit;
}

$user_id = $_SESSION['auth_user_id'];
$query = $mysqli->query("SELECT * FROM `users` WHERE `id` = ".$user_id." AND `auth_code` = '".$mysqli->real_escape_string($code)."' AND `auth_code_expires` > NOW()");

if($user = $query->fetch_assoc()) {
    // Код верный
    $_SESSION['user'] = $user_id;
    unset($_SESSION['auth_user_id']);
    
    // Создаем новую сессию (единая сессия)
    createSession($user_id, $mysqli);
    
    // Очищаем код
    $mysqli->query("UPDATE `users` SET `auth_code` = NULL, `auth_code_expires` = NULL WHERE `id` = ".$user_id);
    
    echo json_encode(["success" => true, "token" => md5(md5($user_id))]);
} else {
    echo json_encode(["success" => false, "message" => "Неверный или просроченный код"]);
}
?>