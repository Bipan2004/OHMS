<?php
session_start();
include 'db_config.php';

// Check if the user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all users with pending status
$users_sql = "SELECT * FROM users WHERE status = 'pending'";
$users_result = $conn->query($users_sql);

// Initialize report data
$report = [];
if (isset($_POST['generate_report'])) {
    $year = $_POST['year'];
    $month = isset($_POST['month']) ? $_POST['month'] : null;

    // Fetch report data for the selected period
    $sql = "SELECT p.name AS patient_name,
                   COUNT(e.exam_id) AS total_exams,
                   SUM(e.result = 'Abnormal') AS abnormal_exams,
                   (SUM(e.result = 'Abnormal') / COUNT(e.exam_id)) * 100 AS abnormal_percentage
            FROM exams e
            JOIN appointments a ON e.appointment_id = a.appointment_id
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE YEAR(e.created_at) = '$year'";

    if ($month) {
        $sql .= " AND MONTH(e.created_at) = '$month'";
    }
    
    $sql .= " GROUP BY p.patient_id";
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $report[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Admin Dashboard</h2>

    <!-- Pending User Approvals Section -->
    <h3>Pending User Approvals</h3>
    <table border="1">
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>User Type</th>
            <th>Actions</th>
        </tr>
        <?php while ($user = $users_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                <td>
                    <!-- Approve Form -->
                    <form action="approve_user.php" method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                        <button type="submit">Approve</button>
                    </form>
                    <!-- Reject Form -->
                    <form action="reject_user.php" method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                        <button type="submit">Reject</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Report Generation Section -->
    <h3>Generate Monthly/Yearly Report</h3>
    <form action="admin_dashboard.php" method="POST">
        <label for="year">Year:</label>
        <input type="number" name="year" required placeholder="e.g., 2024">
        
        <label for="month">Month (optional):</label>
        <input type="number" name="month" min="1" max="12" placeholder="1-12">
        
        <button type="submit" name="generate_report">Generate Report</button>
    </form>

    <!-- Display Report Results -->
    <?php if (!empty($report)): ?>
        <h3>Report Results</h3>
        <table border="1">
            <tr>
                <th>Patient Name</th>
                <th>Total Exams</th>
                <th>Abnormal Exams</th>
                <th>Abnormal Percentage (%)</th>
            </tr>
            <?php foreach ($report as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_exams']); ?></td>
                    <td><?php echo htmlspecialchars($row['abnormal_exams']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($row['abnormal_percentage'], 2)); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif (isset($_POST['generate_report'])): ?>
        <p>No data available for the selected period.</p>
    <?php endif; ?>
</body>
</html>
