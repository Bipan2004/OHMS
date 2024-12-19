<?php
include 'db_config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'staff') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['exam_id']) && isset($_POST['result'])) {
    $exam_id = $_POST['exam_id'];
    $result = $_POST['result'];

    $sql = "UPDATE exams SET result = '$result', status = 'completed' WHERE exam_id = '$exam_id'";

    if ($conn->query($sql) === TRUE) {
        echo "Exam result updated successfully.";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
header("Location: manage_exams.php");
?>
