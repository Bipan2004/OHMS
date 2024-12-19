<?php
session_start();
include 'db_config.php';

var_dump($_POST); // Debugging line to see POST data

// Check if the user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'doctor') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['patient_id']) && isset($_POST['exam_type'])) {
    $user_id = $_SESSION['user_id'];
    $patient_id = $_POST['patient_id'];
    $exam_type = $_POST['exam_type'];

    // Retrieve the doctor's doctor_id based on the user_id
    $doctor_id_query = "SELECT doctor_id FROM doctors WHERE user_id = '$user_id'";
    $doctor_id_result = $conn->query($doctor_id_query);

    if ($doctor_id_result->num_rows > 0) {
        $doctor = $doctor_id_result->fetch_assoc();
        $doctor_id = $doctor['doctor_id'];

        // Insert the monitoring preference
        $monitoring_sql = "INSERT INTO monitoring (doctor_id, patient_id, exam_type) 
                           VALUES ('$doctor_id', '$patient_id', '$exam_type')";
        if ($conn->query($monitoring_sql) === TRUE) {
            header("Location: doctor_dashboard.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Error: Doctor ID not found for this user.";
    }
} else {
    echo "Invalid form data.";
}
?>
