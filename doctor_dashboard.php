<?php
session_start();
include 'db_config.php';

// Check if the user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'doctor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch doctor's profile details
$doctor_sql = "SELECT * FROM doctors WHERE user_id = '$user_id'";
$doctor_result = $conn->query($doctor_sql);
$doctor = $doctor_result->fetch_assoc();

// Fetch doctor's appointments
$appointments_sql = "SELECT appointments.appointment_id, appointments.appointment_date, patients.name AS patient_name, patients.patient_id
                     FROM appointments
                     JOIN patients ON appointments.patient_id = patients.patient_id
                     WHERE appointments.doctor_id = '{$doctor['doctor_id']}'";
$appointments_result = $conn->query($appointments_sql);

// Fetch notifications
$notifications_sql = "SELECT * FROM notifications WHERE doctor_id = '{$doctor['doctor_id']}' AND is_read = 0";
$notifications_result = $conn->query($notifications_sql);

// Fetch monitoring items
$monitoring_sql = "SELECT monitoring.*, patients.name AS patient_name 
                   FROM monitoring 
                   JOIN patients ON monitoring.patient_id = patients.patient_id
                   WHERE doctor_id = '{$doctor['doctor_id']}'";
$monitoring_result = $conn->query($monitoring_sql);

// Search functionality
$search_query = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $patient_name = $_POST['patient_name'] ?? '';
    $exam_date = $_POST['exam_date'] ?? '';
    $exam_item = $_POST['exam_item'] ?? '';
    $abnormal = isset($_POST['abnormal']) ? "AND exams.result = 'Abnormal'" : "";

    $search_query = "SELECT exams.*, patients.name AS patient_name, appointments.appointment_date 
                     FROM exams 
                     JOIN appointments ON exams.appointment_id = appointments.appointment_id
                     JOIN patients ON appointments.patient_id = patients.patient_id
                     WHERE patients.name LIKE '%$patient_name%' 
                     AND appointments.appointment_date LIKE '%$exam_date%'
                     AND exams.exam_type LIKE '%$exam_item%' $abnormal";
    $search_result = $conn->query($search_query);
}

// Update profile information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    $update_sql = "UPDATE doctors SET name='$name', email='$email', phone_number='$phone_number' WHERE user_id='$user_id'";
    $conn->query($update_sql);
    header("Location: doctor_dashboard.php");
    exit();
}

// Delete monitoring item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_monitoring'])) {
    $monitor_id = $_POST['monitor_id'];
    $delete_sql = "DELETE FROM monitoring WHERE monitor_id = '$monitor_id'";
    $conn->query($delete_sql);
    header("Location: doctor_dashboard.php");
    exit();
}

// Mark notification as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    $notification_id = $_POST['notification_id'];
    $mark_as_read_sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = '$notification_id'";
    $conn->query($mark_as_read_sql);
    header("Location: doctor_dashboard.php");
    exit();
}

// Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            margin: 20px auto;
        }

        header {
            background-color: #4CAF50;
            padding: 10px 20px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
        }

        header form {
            margin: 0;
        }

        header form button {
            background-color: #fff;
            color: #4CAF50;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            font-weight: bold;
        }

        header form button:hover {
            background-color: #e2e2e2;
        }

        h2 {
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .appointments, .monitoring, .search-results, .notifications, .profile-section {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: #fff;
        }

        form {
            margin-top: 10px;
        }

        input[type="text"], input[type="date"], input[type="email"], button {
            padding: 10px;
            margin: 5px 0;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <h1>Doctor Dashboard</h1>
        <form method="POST">
            <button type="submit" name="logout">Logout</button>
        </form>
    </header>

    <div class="container">
        <!-- Profile Section -->
        <section class="profile-section">
            <h2>Update Profile</h2>
            <form method="POST">
                <input type="text" name="name" value="<?= htmlspecialchars($doctor['name']) ?>" placeholder="Name" required>
                <input type="email" name="email" value="<?= htmlspecialchars($doctor['email']) ?>" placeholder="Email" required>
                <input type="text" name="phone_number" value="<?= htmlspecialchars($doctor['phone_number']) ?>" placeholder="Phone Number" required>
                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </section>

        <!-- Notifications Section -->
        <section class="notifications">
            <h2>Notifications</h2>
            <ul>
                <?php while ($notification = $notifications_result->fetch_assoc()): ?>
                    <li>
                        <?= htmlspecialchars($notification['message']) ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="notification_id" value="<?= $notification['notification_id'] ?>">
                            <button type="submit" name="mark_as_read">Mark as Read</button>
                        </form>
                    </li>
                <?php endwhile; ?>
                <?php if ($notifications_result->num_rows === 0): ?>
                    <p>No new notifications.</p>
                <?php endif; ?>
            </ul>
        </section>

        <!-- Appointments Section -->
        <section class="appointments">
            <h2>Appointments</h2>
            <table>
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Appointment Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                            <td>
                                <form method="POST" action="prescribe_exam.php" style="display:inline;">
                                    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id'] ?>">
                                    <button type="submit">Prescribe Exam</button>
                                </form>
                                <form method="POST" action="view_history.php" style="display:inline;">
                                    <input type="hidden" name="patient_id" value="<?= $appointment['patient_id'] ?>">
                                    <button type="submit">View History</button>
                                </form>
                                <form method="POST" action="set_monitoring.php" style="display:inline;">
                                    <input type="hidden" name="patient_id" value="<?= $appointment['patient_id'] ?>">
                                    <input type="text" name="exam_type" placeholder="e.g., Liver Function" required>
                                    <button type="submit">Set Monitoring</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Monitoring Section -->
        <section class="monitoring">
            <h2>Monitoring Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Monitoring Item</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($monitoring = $monitoring_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($monitoring['patient_name']) ?></td>
                            <td><?= htmlspecialchars($monitoring['exam_type']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="monitor_id" value="<?= $monitoring['monitor_id'] ?>">
                                    <button type="submit" name="delete_monitoring">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Search Section -->
        <section class="search-results">
            <h2>Search Results</h2>
            <form method="POST">
                <input type="text" name="patient_name" placeholder="Patient Name">
                <input type="date" name="exam_date">
                <input type="text" name="exam_item" placeholder="Exam Type">
                <label><input type="checkbox" name="abnormal"> Show Abnormal Only</label>
                <button type="submit" name="search">Search</button>
            </form>

            <?php if (!empty($search_query)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Exam Type</th>
                            <th>Result</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($search_row = $search_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($search_row['patient_name']) ?></td>
                                <td><?= htmlspecialchars($search_row['exam_type']) ?></td>
                                <td><?= htmlspecialchars($search_row['result']) ?></td>
                                <td><?= htmlspecialchars($search_row['appointment_date']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
