<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'doctor') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];
}

if (isset($_POST['exam_type'])) {
    $exam_type = $_POST['exam_type'];
    $sql = "INSERT INTO exams (appointment_id, exam_type, status) VALUES ('$appointment_id', '$exam_type', 'pending')";
    $conn->query($sql);
    header("Location: doctor_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescribe Exam</title>
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
            max-width: 400px;
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

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            color: #333;
            text-align: left;
        }

        input[type="text"] {
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            border-color: #4CAF50;
            outline: none;
        }

        button {
            padding: 12px;
            font-size: 1em;
            color: #fff;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Prescribe an Exam</h2>
        <form action="" method="POST">
            <label for="exam_type">Exam Type:</label>
            <input type="text" id="exam_type" name="exam_type" required>
            <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
            <button type="submit">Prescribe</button>
        </form>
    </div>
</body>
</html>
