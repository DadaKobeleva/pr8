<?php
session_start();
include("../settings/connect_datebase.php");

// Функция для отправки email
function sendAuthEmail($to, $code) {
    $subject = 'Код авторизации';
    $message = 'Ваш код авторизации: ' . $code;
    $headers = 'From: no-reply@edu.permaviat.ru' . "\r\n" .
               'Reply-To: no-reply@edu.permaviat.ru' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    return mail($to, $subject, $message, $headers);
}

$login = $_POST['login'];
$password = $_POST['password'];

// Проверяем пользователя
$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$mysqli->real_escape_string($login)."'");

$response = ["success" => false, "message" => ""];
$user_data = null;

while($user_read = $query_user->fetch_assoc()) {
    $user_data = $user_read;
}

if($user_data) {
    if(password_verify($password, $user_data['password'])) {
        // Генерируем 6-значный код
        $auth_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Сохраняем код в БД
        $mysqli->query("UPDATE `users` SET `auth_code` = '".$auth_code."', `auth_code_expires` = '".$expires."' WHERE `id` = ".$user_data['id']);
        
        // Отправляем email
        if(sendAuthEmail($user_data['email'], $auth_code)) {
            $_SESSION['auth_user_id'] = $user_data['id'];
            $response = ["success" => true, "message" => "Код отправлен на email"];
        } else {
            $response = ["success" => false, "message" => "Ошибка отправки email"];
        }
    } else {
        $response = ["success" => false, "message" => "Неверный пароль"];
    }
} else {
    $response = ["success" => false, "message" => "Пользователь не найден"];
}

echo json_encode($response);
?>