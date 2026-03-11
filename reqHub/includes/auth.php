<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function requireLogin() {

    if (!isset($_SESSION['user'])) {
        header('Location: /reqHub/public/login.php');
        exit;
    }

}

function requireRole(array $allowedRoles) {

    requireLogin();

    $role = $_SESSION['user']['role'] ?? null;

    if (!in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        exit('Access denied(AUTH)   ');
    }
    //requireLogin();
    //$_userRole = $_SESSION['user']['role'] ?? null;

}