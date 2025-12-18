<?php
session_start();
include("../settings/connect_datebase.php");

// Включите отладку
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Получаем данные
$login = isset($_POST['login']) ? $_POST['login'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';

// Проверяем, что все данные получены
if(empty($login) || empty($password) || empty($email)) {
    echo json_encode([
        "success" => false, 
        "message" => "Все поля обязательны для заполнения"
    ]);
    exit;
}

// Функция для валидации пароля
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Пароль должен содержать минимум 8 символов";
    }
    
    if (!preg_match('/[a-zA-Z]/', $password)) {
        $errors[] = "Пароль должен содержать латинские буквы";
    }
    
    if (!preg_match('/\d/', $password)) {
        $errors[] = "Пароль должен содержать цифры";
    }
    
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = "Пароль должен содержать специальные символы";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Пароль должен содержать хотя бы одну заглавную букву";
    }
    
    return $errors;
}

// Валидация пароля
$passwordErrors = validatePassword($password);
if (count($passwordErrors) > 0) {
    echo json_encode([
        "success" => false, 
        "errors" => $passwordErrors
    ]);
    exit;
}

// Проверяем email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false, 
        "message" => "Неверный формат email"
    ]);
    exit;
}

// Проверяем существование пользователя
$stmt = $mysqli->prepare("SELECT id FROM users WHERE login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0) {
    $stmt->close();
    echo json_encode([
        "success" => false, 
        "message" => "Пользователь с таким логином существует."
    ]);
    exit;
}
$stmt->close();

// Хешируем пароль
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Вставляем пользователя
$stmt = $mysqli->prepare("INSERT INTO users (login, password, roll, email) VALUES (?, ?, 0, ?)");
$stmt->bind_param("sss", $login, $hashed_password, $email);

if($stmt->execute()) {
    $id = $stmt->insert_id;
    $_SESSION['user'] = $id;
    
    echo json_encode([
        "success" => true, 
        "id" => $id
    ]);
} else {
    echo json_encode([
        "success" => false, 
        "message" => "Ошибка базы данных: " . $stmt->error
    ]);
}

$stmt->close();
?>