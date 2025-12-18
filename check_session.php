<?php
// check_session.php
function checkSingleSession($user_id, $mysqli) {
    if(!isset($_SESSION['session_token'])) {
        return false;
    }
    
    $token = $_SESSION['session_token'];
    $query = $mysqli->query("SELECT * FROM `user_sessions` WHERE `user_id` = ".$user_id." AND `session_token` = '".$mysqli->real_escape_string($token)."'");
    
    if($query->num_rows > 0) {
        // Обновляем время последней активности
        $mysqli->query("UPDATE `user_sessions` SET `last_activity` = NOW() WHERE `user_id` = ".$user_id." AND `session_token` = '".$mysqli->real_escape_string($token)."'");
        return true;
    }
    
    return false;
}

function createSession($user_id, $mysqli) {
    // Удаляем старые сессии этого пользователя
    $mysqli->query("DELETE FROM `user_sessions` WHERE `user_id` = ".$user_id);
    
    // Создаем новую сессию
    $token = md5(uniqid(rand(), true));
    $_SESSION['session_token'] = $token;
    
    $mysqli->query("INSERT INTO `user_sessions` (`user_id`, `session_token`) VALUES (".$user_id.", '".$mysqli->real_escape_string($token)."')");
    
    return $token;
}

function destroySession($user_id, $mysqli) {
    if(isset($_SESSION['session_token'])) {
        $token = $_SESSION['session_token'];
        $mysqli->query("DELETE FROM `user_sessions` WHERE `user_id` = ".$user_id." AND `session_token` = '".$mysqli->real_escape_string($token)."'");
        unset($_SESSION['session_token']);
    }
}
?>