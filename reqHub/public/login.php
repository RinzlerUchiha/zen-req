<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If logged in via Zen, go to ReqHub dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: /zen/reqHub/public/dashboard');
    exit;
}

// Not logged in: go to Zen login
header('Location: /zen/login');
exit;