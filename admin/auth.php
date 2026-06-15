<?php
/**
 * Admin guard. Include at the top of any protected admin page or endpoint.
 * Uses the shared auth library and redirects to /admin/login when not signed in.
 */
require_once __DIR__ . '/../login/auth_check.php';
requireAuth('/admin/login');
