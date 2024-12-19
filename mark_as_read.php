<?php
include 'db_config.php';
session_start();

// Check if the user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'doctor') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['notification_id'])) {
    $notification_id = $_POST['notification_id'];

    // Update the notification to mark it as read
    $sql = "UPDATE notifications SET is_read = TRUE WHERE notification_id = '$notification_id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: doctor_dashboard.php");
        exit();
    } else {
        echo "Error updating notification: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
