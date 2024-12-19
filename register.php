<?php include 'db_config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration</title>
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
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            text-align: left;
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"], input[type="email"], input[type="date"], input[type="password"] {
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 5px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="date"]:focus, input[type="password"]:focus {
            border-color: #4CAF50;
            outline: none;
        }

        button[type="submit"] {
            margin-top: 20px;
            padding: 12px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        .message {
            margin-top: 20px;
            font-size: 1em;
            color: #4CAF50;
        }

        .error {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Patient Registration</h2>
        <form action="" method="POST">
            <label>Name:</label>
            <input type="text" name="name" required>
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Phone Number:</label>
            <input type="text" name="phone_number" required>
            <label>Date of Birth:</label>
            <input type="date" name="dob" required>
            <label>Health ID:</label>
            <input type="text" name="health_id" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit" name="register">Register</button>
        </form>

        <?php
        if (isset($_POST['register'])) {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone_number'];
            $dob = $_POST['dob'];
            $health_id = $_POST['health_id'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

            // Insert into Users table
            $sql = "INSERT INTO users (username, email, phone_number, password, user_type, status) 
                    VALUES ('$name', '$email', '$phone', '$password', 'patient', 'pending')";

            if ($conn->query($sql) === TRUE) {
                $user_id = $conn->insert_id;
                $sql = "INSERT INTO patients (user_id, name, dob, health_id, emergency_contact) 
                        VALUES ('$user_id', '$name', '$dob', '$health_id', '$phone')";
                $conn->query($sql);
                echo "<p class='message'>Registration successful. Please wait for admin approval.</p>";
            } else {
                echo "<p class='message error'>Error: " . $conn->error . "</p>";
            }
        }
        ?>
    </div>
</body>
</html>
