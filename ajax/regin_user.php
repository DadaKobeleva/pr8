<?php
session_start();
include("../settings/connect_datebase.php");

$login = isset($_POST['login']) ? trim($_POST['login']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Функция для проверки пароля по всем требованиям
function validatePasswordPHP($password) {
    if(strlen($password) < 9) {
        return "Пароль должен содержать более 8 символов";
    }
    
    if(!preg_match('/[a-zA-Z]/', $password)) {
        return "Пароль должен содержать латинские буквы";
    }
    
    if(!preg_match('/[A-Z]/', $password)) {
        return "Пароль должен содержать хотя бы одну заглавную букву";
    }
    
    if(!preg_match('/[0-9]/', $password)) {
        return "Пароль должен содержать цифры";
    }
    
    if(!preg_match('/[!@#$%^&?*\-_=]/', $password)) {
        return "Пароль должен содержать специальные символы (!@#$%^&?*-_=)";
    }
    
    if(!preg_match('/^[a-zA-Z0-9!@#$%^&?*\-_=]+$/', $password)) {
        return "Пароль содержит недопустимые символы";
    }
    
    return true;
}

// Проверка логина
if(empty($login)) {
    echo "ERROR:Введите логин";
    exit;
}

// Проверка пароля
if(empty($password)) {
    echo "ERROR:Введите пароль";
    exit;
}

// Проверка email
if(empty($email)) {
    echo "ERROR:Введите email";
    exit;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "ERROR:Введите корректный email";
    exit;
}

// Проверка пароля на соответствие требованиям
$passwordValidation = validatePasswordPHP($password);
if($passwordValidation !== true) {
    echo "ERROR_PASSWORD:" . $passwordValidation;
    exit;
}

// Проверяем, не существует ли уже пользователь с таким логином
$check_query = $mysqli->prepare("SELECT id FROM users WHERE login = ?");
$check_query->bind_param("s", $login);
$check_query->execute();
$check_query->store_result();

if($check_query->num_rows > 0) {
    echo "ERROR:Пользователь с таким логином уже существует";
    exit;
}
$check_query->close();

// Проверяем, не существует ли уже пользователь с таким email
$check_email_query = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
$check_email_query->bind_param("s", $email);
$check_email_query->execute();
$check_email_query->store_result();

if($check_email_query->num_rows > 0) {
    echo "ERROR:Пользователь с таким email уже существует";
    exit;
}
$check_email_query->close();

// Хешируем пароль
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Добавляем нового пользователя (теперь с email)
$insert_query = $mysqli->prepare("INSERT INTO users (login, password, email, roll) VALUES (?, ?, ?, 0)");
$insert_query->bind_param("sss", $login, $hashed_password, $email);

if($insert_query->execute()) {
    $user_id = $mysqli->insert_id;
    $_SESSION['user'] = $user_id;
    echo $user_id;
} else {
    // Добавьте отладку для выяснения конкретной ошибки
    error_log("Ошибка MySQL: " . $mysqli->error);
    echo "ERROR:Ошибка при регистрации пользователя. Код ошибки: " . $mysqli->errno;
}

$insert_query->close();
$mysqli->close();
?>