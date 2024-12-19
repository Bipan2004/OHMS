<?php
include 'db_config.php';
session_start();

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $sql = "UPDATE users SET status = 'approved' WHERE user_id = '$user_id'";
    $conn->query($sql);
    header("Location: admin_dashboard.php");
    exit();
}
?>
