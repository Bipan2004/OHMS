<?php
session_start();

include 'db_config.php';

// Check if the user is logged in and is a staff member
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'staff') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch staff details from the users table
$staff_sql = "SELECT username, email, phone_number, working_id, image FROM users WHERE user_id = '$user_id'";
$staff_result = $conn->query($staff_sql);

if (!$staff_result) {
    echo "Error: " . $conn->error;
    exit();
}

$staff = $staff_result->fetch_assoc();

// Handle profile update
if (isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $working_id = $_POST['working_id'];
    $image = $_FILES['image']['name'];

    // File upload handling
    if (!empty($image)) {
        $image_temp = $_FILES['image']['tmp_name'];
        $image_folder = "uploads/" . basename($image);
        move_uploaded_file($image_temp, $image_folder);
    } else {
        $image_folder = $staff['image'];
    }

    $update_sql = "UPDATE users SET 
                    username = '$username', 
                    email = '$email', 
                    phone_number = '$phone_number', 
                    working_id = '$working_id', 
                    image = '$image_folder' 
                  WHERE user_id = '$user_id'";

    if ($conn->query($update_sql) === TRUE) {
        echo "<p class='success-message'>Profile updated successfully.</p>";
        // Refresh staff data
        $staff = array_merge($staff, [
            'username' => $username,
            'email' => $email,
            'phone_number' => $phone_number,
            'working_id' => $working_id,
            'image' => $image_folder
        ]);
    } else {
        echo "<p class='error-message'>Error updating profile: " . $conn->error . "</p>";
    }
}

// Fetch exams assigned to patients for updating
$exams_sql = "SELECT exams.exam_id, exams.exam_type, exams.result, exams.status, 
                     patients.name AS patient_name, appointments.appointment_date 
              FROM exams
              JOIN appointments ON exams.appointment_id = appointments.appointment_id
              JOIN patients ON appointments.patient_id = patients.patient_id";
$exams_result = $conn->query($exams_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<a href="logout.php" class="logout-button">Logout</a>

    <title>Staff Dashboard</title>
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
            padding: 20px;
        }

        .container {
            max-width: 900px;
            width: 100%;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
.logout-button {
        display: inline-block;
        padding: 10px 15px;
        background-color: #e74c3c;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        text-align: center;
    }

    .logout-button:hover {
        background-color: #c0392b;
    }
        h2, h3 {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-section, .exams-section {
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="file"] {
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }

        .form-group img {
            width: 80px;
            height: 80px;
            margin-top: 10px;
            border-radius: 50%;
            object-fit: cover;
        }

        .update-form button {
            padding: 8px 15px;
            font-size: 1em;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .update-form button:hover {
            background-color: #45a049;
        }

        /* Styled Table */
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

        .no-exams {
            color: #888;
            font-size: 1.1em;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Staff Dashboard</h2>

        <!-- Profile Section -->
        <section class="profile-section">
            <h3>Your Profile</h3>
            <form action="staff_dashboard.php" method="POST" enctype="multipart/form-data" class="update-form">
                <div class="form-group">
                    <label for="username">Name:</label>
                    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($staff['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($staff['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number:</label>
                    <input type="text" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($staff['phone_number']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="working_id">Working ID:</label>
                    <input type="text" name="working_id" id="working_id" value="<?php echo htmlspecialchars($staff['working_id']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="image">Profile Picture:</label>
                    <input type="file" name="image" id="image">
                    <?php if (!empty($staff['image'])): ?>
                        <img src="<?php echo htmlspecialchars($staff['image']); ?>" alt="Profile Picture">
                    <?php endif; ?>
                </div>
                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </section>

        <!-- Exams Section -->
        <section class="exams-section">
            <h3>Manage Patient Exams</h3>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Appointment Date</th>
                        <th>Exam Type</th>
                        <th>Result</th>
                        <th>Status</th>
                        <th>Update Result</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($exams_result->num_rows > 0) {
                        while ($exam = $exams_result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($exam['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($exam['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($exam['exam_type']); ?></td>
                                <td><?php echo htmlspecialchars($exam['result']); ?></td>
                                <td><?php echo htmlspecialchars($exam['status']); ?></td>
                                <td>
                                    <form action="update_exam_result.php" method="POST" class="update-form">
                                        <input type="hidden" name="exam_id" value="<?php echo $exam['exam_id']; ?>">
                                        <input type="text" name="result" placeholder="Enter result" required>
                                        <button type="submit">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='6' class='no-exams'>No exams available to display.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html> 