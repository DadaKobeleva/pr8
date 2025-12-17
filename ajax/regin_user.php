<?php
session_start();
include("../settings/connect_datebase.php");

$login = isset($_POST['login']) ? trim($_POST['login']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Функция для проверки пароля по всем требованиям
function validatePasswordPHP($password) {
    // 1. Проверка длины (более 8 символов = минимум 9)
    if(strlen($password) < 9) {
        return "Пароль должен содержать более 8 символов";
    }
    
    // 2. Проверка наличия латинских букв
    if(!preg_match('/[a-zA-Z]/', $password)) {
        return "Пароль должен содержать латинские буквы";
    }
    
    // 3. Проверка наличия заглавной буквы
    if(!preg_match('/[A-Z]/', $password)) {
        return "Пароль должен содержать хотя бы одну заглавную букву";
    }
    
    // 4. Проверка наличия цифр
    if(!preg_match('/[0-9]/', $password)) {
        return "Пароль должен содержать цифры";
    }
    
    // 5. Проверка наличия специальных символов
    if(!preg_match('/[!@#$%^&?*\-_=]/', $password)) {
        return "Пароль должен содержать специальные символы (!@#$%^&?*-_=)";
    }
    
    // 6. Дополнительная проверка на разрешенные символы
    if(!preg_match('/^[a-zA-Z0-9!@#$%^&?*\-_=]+$/', $password)) {
        return "Пароль содержит недопустимые символы";
    }
    
    return true;
}

if(empty($login)) {
    echo "Введите логин";
    exit;
}

if(empty($password)) {
    echo "Введите пароль";
    exit;
}

$passwordValidation = validatePasswordPHP($password);
if($passwordValidation !== true) {
    echo "ERROR_PASSWORD:" . $passwordValidation;
    exit;
}

$check_query = $mysqli->prepare("SELECT id FROM users WHERE login = ?");
$check_query->bind_param("s", $login);
$check_query->execute();
$check_query->store_result();

if($check_query->num_rows > 0) {
    echo "Пользователь с таким логином уже существует";
    exit;
}
$check_query->close();

$plain_password = $password;

$insert_query = $mysqli->prepare("INSERT INTO users (login, password, roll) VALUES (?, ?, 0)");
$insert_query->bind_param("ss", $login, $plain_password);

if($insert_query->execute()) {
    $user_id = $mysqli->insert_id;
    $_SESSION['user'] = $user_id;
    echo $user_id;
} else {
    echo "Ошибка при регистрации пользователя";
}

$insert_query->close();
?>