<?php
$_SESSION = [];
session_destroy();
header('Location: /admin/login');
exit;

