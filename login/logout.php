<?php
/**
 * Logout Handler
 * Clears session and redirects to login
 */

require_once 'auth_check.php';

// Logout the user
logout();

// Redirect to login page
header('Location: login.php?message=logged_out');
exit();
?>