<?php
include 'db_config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Fetch appointments based on user type
if ($user_type == 'patient') {
    $sql = "SELECT appointments.*, doctors.name AS doctor_name FROM appointments
            JOIN doctors ON appointments.doctor_id = doctors.doctor_id
            WHERE appointments.patient_id = (SELECT patient_id FROM patients WHERE user_id = '$user_id')";
} elseif ($user_type == 'doctor') {
    $sql = "SELECT appointments.*, patients.name AS patient_name FROM appointments
            JOIN patients ON appointments.patient_id = patients.patient_id
            WHERE appointments.doctor_id = (SELECT doctor_id FROM doctors WHERE user_id = '$user_id')";
} else {
    echo "Access Denied.";
    exit();
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Appointments</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Appointments</h2>

    <table border="1">
        <tr>
            <th>Appointment Date</th>
            <?php if ($user_type == 'patient') { ?>
                <th>Doctor</th>
            <?php } else { ?>
                <th>Patient</th>
            <?php } ?>
            <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['appointment_date']; ?></td>
                <td><?php echo $user_type == 'patient' ? $row['doctor_name'] : $row['patient_name']; ?></td>
                <td><?php echo $row['status']; ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
