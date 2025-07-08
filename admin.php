<?php
/**
 * Admin Dashboard Redirect
 * Quick access point to the email service admin area
 */

// Protect this page with authentication
require_once 'login/auth_check.php';

// If authenticated, redirect to email service dashboard
header('Location: emailservice/index.php');
exit();
?>