<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Session not set. Redirecting to login.";
    header("Location: login.php");
    exit();
}

echo "Welcome to the dashboard, user ID: " . $_SESSION['user_id'];
?>
