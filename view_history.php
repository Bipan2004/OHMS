<?php
session_start();
include 'db_config.php';

// Check if the user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'doctor') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['patient_id'])) {
    $patient_id = $_POST['patient_id'];

    // Fetch patient history
    $history_sql = "SELECT exams.exam_type, exams.result, exams.status 
                    FROM exams
                    JOIN appointments ON exams.appointment_id = appointments.appointment_id
                    WHERE appointments.patient_id = '$patient_id'";
    $history_result = $conn->query($history_sql);

    if (!$history_result) {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient History</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f9;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #4CAF50;
            font-size: 1.8em;
            margin-bottom: 20px;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 1em;
        }

        .styled-table th, .styled-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .styled-table th {
            background-color: #4CAF50;
            color: #fff;
        }

        .styled-table tr:nth-child(even) {
            background-color: #f3f3f3;
        }

        .styled-table tr:hover {
            background-color: #f1f1f1;
        }

        .no-history {
            color: #888;
            font-size: 1.1em;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Patient History</h2>
        <?php if (isset($history_result) && $history_result->num_rows > 0): ?>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Exam Type</th>
                        <th>Result</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($exam = $history_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($exam['exam_type']); ?></td>
                            <td><?php echo htmlspecialchars($exam['result']); ?></td>
                            <td><?php echo htmlspecialchars($exam['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-history">No exam history available for this patient.</p>
        <?php endif; ?>
    </div>
</body>
</html>
