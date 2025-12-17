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
$stmt = $mysqli->prepare("SELECT id, password, email FROM users WHERE login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$stmt->bind_result($id, $hashed_password, $user_email);
$stmt->store_result();

if($stmt->num_rows > 0) {
    $stmt->fetch();
    
    if(password_verify($password, $hashed_password)) {
        // Проверяем, есть ли email у пользователя
        if(empty($user_email) || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            echo "ERROR_EMAIL_NOT_FOUND";
            $stmt->close();
            exit;
        }
        
        // ГЕНЕРАЦИЯ КОДА (6 цифр)
        $auth_code = sprintf("%06d", rand(0, 999999));
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Сохраняем код в БД
        $update_stmt = $mysqli->prepare("UPDATE users SET auth_code = ?, auth_code_expires = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $auth_code, $expires, $id);
        $update_stmt->execute();
        $update_stmt->close();
        
        // ========== ОТПРАВКА EMAIL ==========
        $subject = "Код авторизации для входа на сайт";
        $message = "
        Ваш код подтверждения для входа: <strong>$auth_code</strong>
        
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
            $_SESSION['pending_user_id'] = $id;
            $_SESSION['pending_user_email'] = $user_email;
            $_SESSION['last_code_time'] = time();
            echo "NEED_CODE";
        } else {
            // Ошибка отправки email
            error_log("Ошибка отправки email на $user_email");
            
            // Можно показать код в демо-режиме или выдать ошибку
            $_SESSION['pending_user_id'] = $id;
            $_SESSION['pending_user_email'] = $user_email;
            $_SESSION['demo_auth_code'] = $auth_code;
            $_SESSION['last_code_time'] = time();
            
            // Выберите один из вариантов:
            // Вариант 1: Показать ошибку
            // echo "ERROR_EMAIL_SEND";
            
            // Вариант 2: Демо-режим (показываем код)
            echo "NEED_CODE_DEMO:$auth_code";
        }
        
    } else {
        echo "ERROR_AUTH";
    }
} else {
    echo "ERROR_AUTH";
}
$stmt->close();
?>