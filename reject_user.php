<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['username'])) {
    $username = $_POST['username'];
    $sql = "DELETE FROM users WHERE username = '$username'";

    if ($conn->query($sql) === TRUE) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Error rejecting user: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
