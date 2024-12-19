<?php
include 'db_config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_type = $_SESSION['user_type'];

// Only allow doctors and staff to access this page
if ($user_type != 'doctor' && $user_type != 'staff') {
    echo "Access Denied.";
    exit();
}

// Fetch all exams
$sql = "SELECT exams.*, patients.name AS patient_name, appointments.appointment_date 
        FROM exams
        JOIN appointments ON exams.appointment_id = appointments.appointment_id
        JOIN patients ON appointments.patient_id = patients.patient_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Exams</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Manage Exams</h2>

    <table border="1">
        <tr>
            <th>Patient</th>
            <th>Appointment Date</th>
            <th>Exam Type</th>
            <th>Result</th>
            <th>Status</th>
            <?php if ($user_type == 'staff') { ?>
                <th>Update Result</th>
            <?php } ?>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['patient_name']; ?></td>
                <td><?php echo $row['appointment_date']; ?></td>
                <td><?php echo $row['exam_type']; ?></td>
                <td><?php echo $row['result']; ?></td>
                <td><?php echo $row['status']; ?></td>
                <?php if ($user_type == 'staff') { ?>
                    <td>
                        <form action="update_exam.php" method="POST">
                            <input type="hidden" name="exam_id" value="<?php echo $row['exam_id']; ?>">
                            <input type="text" name="result" placeholder="Enter result" required>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
