<?php
session_start();
if(isset($_SESSION['pending_user_id'])) {
    unset($_SESSION['pending_user_id']);
}
echo "OK";
?>