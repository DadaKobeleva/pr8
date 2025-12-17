<?php
session_start();
include("../settings/connect_datebase.php");

if(!isset($_SESSION['pending_user_id']) || !isset($_SESSION['pending_user_email'])) {
    echo "ERROR_SESSION";
    exit;
}

$user_id = $_SESSION['pending_user_id'];
$user_email = $_SESSION['pending_user_email'];

// Проверяем время последней отправки (защита от спама)
$current_time = time();
if(isset($_SESSION['last_code_time']) && ($current_time - $_SESSION['last_code_time']) < 60) {
    echo "ERROR_TOO_SOON";
    exit;
}

// Генерируем новый код
$new_auth_code = sprintf("%06d", rand(0, 999999));
$expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Обновляем код в БД
$update_stmt = $mysqli->prepare("UPDATE users SET auth_code = ?, auth_code_expires = ? WHERE id = ?");
$update_stmt->bind_param("ssi", $new_auth_code, $expires, $user_id);
$update_stmt->execute();
$update_stmt->close();

// ========== ОТПРАВКА EMAIL ==========
$subject = "Новый код авторизации для входа на сайт";
$message = "
Ваш новый код подтверждения для входа: <strong>$new_auth_code</strong>

Код действителен 10 минут до $expires.

Если вы не запрашивали этот код, проигнорируйте это письмо.

С уважением,
Администрация сайта
";

// Заголовки для HTML-письма
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=utf-8\r\n";
$headers .= "From: Система безопасности <noreply@ваш-сайт.ru>\r\n";
$headers .= "Reply-To: no-reply@ваш-сайт.ru\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Отправляем email
$email_sent = mail($user_email, $subject, nl2br($message), $headers);

if($email_sent) {
    // Email отправлен успешно
    $_SESSION['last_code_time'] = time();
    unset($_SESSION['demo_auth_code']); // Удаляем старый демо-код
    echo "SUCCESS";
} else {
    // Ошибка отправки email
    error_log("Ошибка повторной отправки email на $user_email");
    
    // Сохраняем демо-код для тестирования
    $_SESSION['demo_auth_code'] = $new_auth_code;
    $_SESSION['last_code_time'] = time();
    
    // Демо-режим
    echo "DEMO_CODE:$new_auth_code";
}
?>