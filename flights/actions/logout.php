<?php
if(session_status() === PHP_SESSION_NONE) session_start(); // Start the session
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to login page or homepage
header("Location: /zen"); // Change this to your login or homepage URL
exit();