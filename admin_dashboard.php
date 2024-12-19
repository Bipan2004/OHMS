<?php
session_start();
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

include 'db_config.php';

// Check if the user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all users with pending status
$pending_users_sql = "SELECT * FROM users WHERE status = 'pending'";
$pending_users_result = $conn->query($pending_users_sql);

// Handle new doctor/staff account creation
if (isset($_POST['create_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $user_type = $_POST['user_type'];
    $working_id = $_POST['working_id'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, email, phone_number, password, user_type, status, working_id) 
            VALUES ('$name', '$email', '$phone', '$password', '$user_type', 'approved', '$working_id')";
    if ($conn->query($sql) === TRUE) {
        echo "<p class='success-message'>New $user_type account created successfully.</p>";
    } else {
        echo "<p class='error-message'>Error creating account: " . $conn->error . "</p>";
    }
}

// Handle approval, rejection, or deletion of users
if (isset($_POST['approve_user'])) {
    $user_id = $_POST['user_id'];
    $update_sql = "UPDATE users SET status = 'approved' WHERE user_id = '$user_id'";
    $conn->query($update_sql);
    header("Location: admin_dashboard.php");
    exit();
} elseif (isset($_POST['reject_user']) || isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $delete_sql = "DELETE FROM users WHERE user_id = '$user_id'";
    $conn->query($delete_sql);
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch all approved users
$approved_users_sql = "SELECT * FROM users WHERE status = 'approved'";
$approved_users_result = $conn->query($approved_users_sql);

// Report Generation Logic
$report = [];
if (isset($_POST['generate_report'])) {
    $year = $_POST['year'];
    $month = isset($_POST['month']) ? $_POST['month'] : null;

    $sql = "SELECT p.name AS patient_name,
                   COUNT(e.exam_id) AS total_exams,
                   SUM(e.result = 'Abnormal') AS abnormal_exams,
                   (SUM(e.result = 'Abnormal') / COUNT(e.exam_id)) * 100 AS abnormal_percentage
            FROM exams e
            JOIN appointments a ON e.appointment_id = a.appointment_id
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE YEAR(a.appointment_date) = '$year'";

    if ($month) {
        $sql .= " AND MONTH(a.appointment_date) = '$month'";
    }

    $sql .= " GROUP BY p.patient_id";
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $report[] = $row;
        }
    }
}
// December Predict Report Logic
$predict_report = [];
if (isset($_POST['generate_december_report'])) {
    $sql = "SELECT p.name AS patient_name,
                   COUNT(e.exam_id) AS total_exams,
                   SUM(e.result = 'Abnormal') AS abnormal_exams,
                   'Likely health issues based on history' AS prediction
            FROM exams e
            JOIN appointments a ON e.appointment_id = a.appointment_id
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE MONTH(a.appointment_date) = 12 AND YEAR(a.appointment_date) = YEAR(CURDATE())
            GROUP BY p.patient_id";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $predict_report[] = $row;
        }
    }
}


// Fetch all exams for deletion option
$exams_sql = "SELECT exams.exam_id, exams.exam_type, exams.result, exams.status, patients.name AS patient_name
              FROM exams
              JOIN appointments ON exams.appointment_id = appointments.appointment_id
              JOIN patients ON appointments.patient_id = patients.patient_id";
$exams_result = $conn->query($exams_sql);

// Handle exam deletion
if (isset($_POST['delete_exam'])) {
    $exam_id = $_POST['exam_id'];

    // Delete related notifications first to avoid foreign key constraint errors
    $delete_notifications_sql = "DELETE FROM notifications WHERE exam_id = '$exam_id'";
    $conn->query($delete_notifications_sql);

    // Now delete the exam
    $delete_exam_sql = "DELETE FROM exams WHERE exam_id = '$exam_id'";
    $conn->query($delete_exam_sql);
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <style>
        /* General Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background-color: #f0f2f5; color: #333; }
        .container { width: 90%; max-width: 1200px; margin: 20px auto; padding: 20px; background-color: #fff; border-radius: 10px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); }
        h1, h2 { color: #4CAF50; text-align: center; margin-bottom: 20px; }
        .section { margin: 20px 0; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); }

        /* Table Styles */
        .styled-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .styled-table th, .styled-table td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; }
        .styled-table thead tr { background-color: #4CAF50; color: #fff; }
        .styled-table tbody tr:nth-child(even) { background-color: #f3f3f3; }
        .styled-table tbody tr:hover { background-color: #f1f1f1; }
        
        /* Form Styles */
        .form-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        .form-group label { font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 1em; }
        
        /* Button Styles */
        button { padding: 10px 20px; font-size: 1em; color: #fff; background-color: #4CAF50; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; }
        button:hover { background-color: #45a049; }
        .btn-reject { background-color: #e74c3c; }
        .btn-reject:hover { background-color: #c0392b; }
        .btn-delete { background-color: #d9534f; }
        .btn-delete:hover { background-color: #c9302c; }

        /* Message Styles */
        .success-message, .error-message { margin-top: 10px; font-size: 1.1em; text-align: center; }
        .success-message { color: #4CAF50; }
        .error-message { color: #e74c3c; }
.logout-button {
    background-color: #e74c3c;
    color: #fff;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 5px;
    font-size: 1em;
    font-weight: bold;
    float: right;
}

.logout-button:hover {
    background-color: #c0392b;
}

    </style>
</head>
<body>
<div class="container">
    <h1>Admin Dashboard</h1>
<a href="admin_dashboard.php?logout=true" class="logout-button">Logout</a>

    <!-- Pending User Approvals Section -->
    <section class="section">
        <h2>Pending User Approvals</h2>
        <table class="styled-table">
            <thead>
                <tr><th>Username</th><th>Email</th><th>User Type</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($user = $pending_users_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                        <td>
                            <form action="admin_dashboard.php" method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" name="approve_user">Approve</button>
                                <button type="submit" name="reject_user" class="btn-reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>

    <!-- Create New Doctor/Staff User Section -->
    <section class="section">
        <h2>Create New Doctor/Staff Account</h2>
        <form action="admin_dashboard.php" method="POST">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="text" name="phone" id="phone" required>
            </div>
            <div class="form-group">
                <label for="user_type">User Type:</label>
                <select name="user_type" id="user_type" required>
                    <option value="doctor">Doctor</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            <div class="form-group">
                <label for="working_id">Working ID:</label>
                <input type="text" name="working_id" id="working_id" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" name="create_user">Create Account</button>
        </form>
    </section>

    <!-- Generate Report Section -->
    <section class="section">
        <h2>Generate Monthly/Yearly Report</h2>
        <form action="admin_dashboard.php" method="POST">
            <div class="form-group">
                <label for="year">Year:</label>
                <input type="number" name="year" required placeholder="e.g., 2024">
            </div>
            <div class="form-group">
                <label for="month">Month (optional):</label>
                <input type="number" name="month" min="1" max="12" placeholder="1-12">
            </div>
            <button type="submit" name="generate_report">Generate Report</button>
        </form>
    </section>

<section class="section">
    <h2>Generate December Predict Report</h2>
    <form method="POST">
        <button type="submit" name="generate_december_report">Generate Report</button>
    </form>
    <?php if (!empty($predict_report)): ?>
        <table class="styled-table">
            <thead>
                <tr><th>Patient Name</th><th>Total Exams</th><th>Abnormal Exams</th><th>Prediction</th></tr>
            </thead>
            <tbody>
                <?php foreach ($predict_report as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_exams']); ?></td>
                        <td><?php echo htmlspecialchars($row['abnormal_exams']); ?></td>
                        <td><?php echo htmlspecialchars($row['prediction']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (isset($_POST['generate_december_report'])): ?>
        <p>No data available for the December report.</p>
    <?php endif; ?>
</section>

    <!-- Display Report Results -->
    <?php if (!empty($report)): ?>
        <section class="section">
            <h2>Report Results</h2>
            <table class="styled-table">
                <thead>
                    <tr><th>Patient Name</th><th>Total Exams</th><th>Abnormal Exams</th><th>Abnormal Percentage (%)</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($report as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['total_exams']); ?></td>
                            <td><?php echo htmlspecialchars($row['abnormal_exams']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['abnormal_percentage'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php elseif (isset($_POST['generate_report'])): ?>
        <p class="message">No data available for the selected period.</p>
    <?php endif; ?>

    <!-- Display All Approved Users -->
    <section class="section">
        <h2>All Approved Users</h2>
        <table class="styled-table">
            <thead>
                <tr><th>Username</th><th>Email</th><th>Phone</th><th>User Type</th><th>Working ID</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($user = $approved_users_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                        <td><?php echo htmlspecialchars($user['working_id']); ?></td>
                        <td>
                            <form action="admin_dashboard.php" method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" name="delete_user" class="btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>

    <!-- Exam Deletion Section -->
    <section class="section">
        <h2>Manage Exam Results</h2>
        <table class="styled-table">
            <thead>
                <tr><th>Patient Name</th><th>Exam Type</th><th>Result</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while ($exam = $exams_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($exam['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($exam['exam_type']); ?></td>
                        <td><?php echo htmlspecialchars($exam['result']); ?></td>
                        <td><?php echo htmlspecialchars($exam['status']); ?></td>
                        <td>
                            <form action="admin_dashboard.php" method="POST" style="display:inline;">
                                <input type="hidden" name="exam_id" value="<?php echo $exam['exam_id']; ?>">
                                <button type="submit" name="delete_exam" class="btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>
</div>
</body>
</html>
