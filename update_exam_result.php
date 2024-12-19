<?php
include 'db_config.php';
session_start();

// Check if the user is logged in and is a staff member
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'staff') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['exam_id']) && isset($_POST['result'])) {
    $exam_id = $_POST['exam_id'];
    $result = $_POST['result'];

    // Update the result in the exams table
    $sql = "UPDATE exams SET result = '$result', status = 'completed' WHERE exam_id = '$exam_id'";

    if ($conn->query($sql) === TRUE) {
        echo "Exam result updated successfully.<br>"; // Debug message

        // Check for monitored tests with abnormal results
        $check_sql = "SELECT m.doctor_id, a.patient_id, e.exam_type
                      FROM monitoring m
                      JOIN exams e ON e.exam_type = m.exam_type
                      JOIN appointments a ON e.appointment_id = a.appointment_id
                      WHERE e.exam_id = '$exam_id' AND e.result = 'Abnormal'";
        
        $check_result = $conn->query($check_sql);
        if ($check_result->num_rows > 0) {
            echo "Abnormal monitored result detected.<br>"; // Debug message

            while ($monitor = $check_result->fetch_assoc()) {
                $doctor_id = $monitor['doctor_id'];
                $patient_id = $monitor['patient_id'];
                $exam_type = $monitor['exam_type'];
                $message = "Abnormal result detected for monitored exam: $exam_type";

                // Insert notification
                $notification_sql = "INSERT INTO notifications (doctor_id, patient_id, exam_id, message) 
                                     VALUES ('$doctor_id', '$patient_id', '$exam_id', '$message')";

                if ($conn->query($notification_sql) === TRUE) {
                    echo "Notification created successfully.<br>"; // Debug message
                } else {
                    echo "Error creating notification: " . $conn->error . "<br>";
                }
            }
        } else {
            echo "No abnormal monitored result found or result is not 'Abnormal'.<br>"; // Debug message
        }

        header("Location: staff_dashboard.php");
        exit();
    } else {
        echo "Error updating result: " . $conn->error . "<br>";
    }
} else {
    echo "Invalid form data.";
}
?>
