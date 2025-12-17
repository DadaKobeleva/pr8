<?php
session_start();
include("../settings/connect_datebase.php");

if(!isset($_SESSION['pending_user_id'])) {
    echo "ERROR_SESSION";
    exit;
}

$code = isset($_POST['code']) ? trim($_POST['code']) : '';

if(empty($code) || !preg_match('/^\d{6}$/', $code)) {
    echo "ERROR_CODE";
    exit;
}

$user_id = $_SESSION['pending_user_id'];

// Проверяем код
$stmt = $mysqli->prepare("SELECT id, auth_code_expires FROM users WHERE id = ? AND auth_code = ?");
$stmt->bind_param("is", $user_id, $code);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows > 0) {
    $stmt->bind_result($id, $expires);
    $stmt->fetch();
    
    // Проверяем срок действия кода
    $current_time = time();
    $expires_time = strtotime($expires);
    
    if($expires_time > $current_time) {
        // Код верный и не истек
        $_SESSION['user'] = $id;
        
        // Очищаем код после успешной проверки
        $clear_stmt = $mysqli->prepare("UPDATE users SET auth_code = NULL, auth_code_expires = NULL WHERE id = ?");
        $clear_stmt->bind_param("i", $id);
        $clear_stmt->execute();
        $clear_stmt->close();
        
        // Очищаем сессию
        unset($_SESSION['pending_user_id']);
        unset($_SESSION['pending_user_email']);
        unset($_SESSION['last_code_time']);
        if(isset($_SESSION['demo_auth_code'])) unset($_SESSION['demo_auth_code']);
        
        // Записываем время входа
        $login_time = date('Y-m-d H:i:s');
        $log_stmt = $mysqli->prepare("UPDATE users SET last_login = ? WHERE id = ?");
        $log_stmt->bind_param("si", $login_time, $id);
        $log_stmt->execute();
        $log_stmt->close();
        
        echo "SUCCESS:$id";
    } else {
        // Код истек
        echo "ERROR_EXPIRED";
    }
} else {
    // Неверный код
    echo "ERROR_CODE";
}
$stmt->close();
?>