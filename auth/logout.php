<?php
require_once '../config.php';
if (isLoggedIn()) {
    logActivity('logout', 'User logged out');
}
session_unset();
session_destroy();
redirect($base_url . 'auth/login.php');
?>
