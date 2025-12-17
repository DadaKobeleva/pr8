<?php
session_start();
include("../settings/connect_datebase.php");

$login = isset($_POST['login']) ? trim($_POST['login']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if(empty($login) || empty($password)) {
    echo "ERROR_EMPTY";
    exit;
}

// Ищем пользователя по логину
$stmt = $mysqli->prepare("SELECT id, password FROM users WHERE login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$stmt->bind_result($id, $hashed_password);
$stmt->store_result();

if($stmt->num_rows > 0) {
    $stmt->fetch();
    
    if(password_verify($password, $hashed_password)) {
        $_SESSION['user'] = $id;
        echo md5(md5($id));
    } else {
        echo "ERROR_AUTH";
    }
} else {
    echo "ERROR_AUTH";
}
$stmt->close();
?>