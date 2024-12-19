<?php
session_start();
include 'db_config.php';

// Check if the user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch patient details, including information from the users table
$patient_sql = "SELECT p.patient_id, p.name, p.health_id, p.dob, p.emergency_contact, u.email, u.phone_number, u.address
                FROM patients p
                JOIN users u ON p.user_id = u.user_id
                WHERE u.user_id = '$user_id'";
$patient_result = $conn->query($patient_sql);

if (!$patient_result) {
    echo "Error: " . $conn->error;
    exit();
}

$patient = $patient_result->fetch_assoc();

// Update patient information if form is submitted
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $emergency_contact = $_POST['emergency_contact'];
    $dob = $_POST['dob'];

    // Update in `users` table
    $update_user_sql = "UPDATE users SET email='$email', phone_number='$phone_number', address='$address' WHERE user_id='$user_id'";
    $conn->query($update_user_sql);

    // Update in `patients` table
    $update_patient_sql = "UPDATE patients SET name='$name', dob='$dob', emergency_contact='$emergency_contact' WHERE user_id='$user_id'";
    $conn->query($update_patient_sql);

    // Refresh page to show updated information
    header("Location: patient_dashboard.php");
    exit();
}

// Handle search functionality
$search_condition = "";
if (isset($_POST['search'])) {
    $search_by = $_POST['search_by'];
    $search_value = $_POST['search_value'];

    if ($search_by === "date") {
        $search_condition = "AND a.appointment_date = '$search_value'";
    } elseif ($search_by === "item") {
        $search_condition = "AND e.exam_type LIKE '%$search_value%'";
    } elseif ($search_by === "abnormal") {
        $search_condition = "AND e.result = 'Abnormal'";
    }
}

// Fetch exam results using the patient_id from the patients table
$exams_sql = "SELECT e.exam_type, e.result, e.status, a.appointment_date
              FROM exams e
              JOIN appointments a ON e.appointment_id = a.appointment_id
              WHERE a.patient_id = '{$patient['patient_id']}' $search_condition
              ORDER BY a.appointment_date DESC";
$exams_result = $conn->query($exams_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logout-button {
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        h2 {
            color: #4CAF50;
            text-align: center;
        }

        .profile-section h3 {
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .profile-form {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 10px 20px;
        }

        .profile-form label {
            display: flex;
            align-items: center;
            font-weight: bold;
            color: #333;
        }

        .profile-form input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .profile-form button {
            grid-column: span 2;
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }

        .exam-section {
            margin-top: 30px;
        }

        .search-form {
            margin-bottom: 20px;
        }

        .search-form select, .search-form input, .search-form button {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #4CAF50;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h2>Welcome, <?php echo htmlspecialchars($patient['name'] ?? ''); ?></h2>
            <a href="logout.php" class="logout-button">Logout</a>
        </header>

        <section class="profile-section">
            <h3>Update Your Profile</h3>
            <form method="POST" class="profile-form">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($patient['name']); ?>" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($patient['email']); ?>" required>
                <label for="phone_number">Phone Number:</label>
                <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($patient['phone_number']); ?>" required>
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($patient['address']); ?>">
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($patient['dob']); ?>" required>
                <label for="emergency_contact">Emergency Contact:</label>
                <input type="text" id="emergency_contact" name="emergency_contact" value="<?php echo htmlspecialchars($patient['emergency_contact']); ?>">
                <button type="submit" name="update_profile">Save Changes</button>
            </form>
        </section>

        <section class="exam-section">
            <h3>Your Exam Results</h3>
            <form method="POST" class="search-form">
                <select name="search_by" required>
                    <option value="date">By Date</option>
                    <option value="item">By Exam Item</option>
                    <option value="abnormal">Abnormal Results</option>
                </select>
                <input type="text" name="search_value" placeholder="Search value" required>
                <button type="submit" name="search">Search</button>
            </form>

            <?php if ($exams_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Exam Type</th>
                            <th>Result</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $exams_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['exam_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['result']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No exam results found.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
