<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Management System</title>
    <style>
        /* Reset and Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            line-height: 1.6;
            background-color: #f4f4f9;
        }

        /* Navigation Bar */
        header {
            background-color: #4CAF50;
            padding: 20px;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5em;
            font-weight: bold;
            color: #fff;
        }

        nav ul {
            list-style: none;
            display: flex;
        }

        nav ul li {
            margin-left: 20px;
        }

        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 1em;
            transition: color 0.3s ease;
        }

        nav ul li a:hover {
            color: #d4d4d4;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.8)), url('images/clinic.png') center/cover;
            color: #333;
            text-align: center;
            padding: 100px 20px;
        }

        .hero-content h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            color: #000; /* Changed to black */
        }

        .hero-content p {
            font-size: 1.2em;
            margin-bottom: 20px;
            max-width: 600px;
            margin: 20px auto;
            color: #000; /* Changed to black */
        }

        .btn-primary {
            padding: 12px 25px;
            font-size: 1.1em;
            color: #fff;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #45a049;
        }

        /* Features Section */
        .features {
            padding: 50px 20px;
            text-align: center;
            background-color: #fff;
        }

        .features h2 {
            font-size: 2em;
            color: #333;
            margin-bottom: 20px;
        }

        .feature-grid {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            flex-wrap: wrap;
        }

        .feature-item {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
            width: 300px;
            max-width: 100%;
        }

        .feature-item h3 {
            font-size: 1.5em;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .feature-item p {
            font-size: 1em;
            color: #555;
            margin-bottom: 15px;
        }

        .btn-secondary {
            font-size: 0.9em;
            color: #4CAF50;
            text-decoration: none;
            padding: 10px 20px;
            border: 2px solid #4CAF50;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #4CAF50;
            color: #fff;
        }

        /* Footer */
        footer {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        footer a {
            color: #4CAF50;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<header>
    <nav>
        <div class="logo">Clinic Management</div>
        <ul>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </nav>
</header>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Welcome to Our Clinic Management System</h1>
        <p>Manage your health effortlessly with our state-of-the-art system designed for patients, doctors, and staff.</p>
        <a href="login.php" class="btn-primary">Get Started</a>
    </div>
</section>

<!-- Feature Highlights -->
<section class="features">
    <h2>Our Features</h2>
    <div class="feature-grid">
        <div class="feature-item">
            <h3>Patient Dashboard</h3>
            <p>View exam results, update information, and more in a user-friendly patient dashboard.</p>
            <a href="patient_dashboard.php" class="btn-secondary">Learn More</a>
        </div>
        <div class="feature-item">
            <h3>Doctor Dashboard</h3>
            <p>Manage patient appointments, prescribe exams, and monitor health indicators.</p>
            <a href="doctor_dashboard.php" class="btn-secondary">Learn More</a>
        </div>
        <div class="feature-item">
            <h3>Staff Dashboard</h3>
            <p>Manage patient exam results, update profiles, and collaborate with doctors effectively.</p>
            <a href="staff_dashboard.php" class="btn-secondary">Learn More</a>
        </div>
        <div class="feature-item">
            <h3>Admin Dashboard</h3>
            <p>Approve new users, generate reports, and oversee clinic operations seamlessly.</p>
            <a href="admin_dashboard.php" class="btn-secondary">Learn More</a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer id="contact">
    <div class="footer-content">
        <p>&copy; 2024 Clinic Management System. All rights reserved.</p>
        <p>Contact us at <a href="mailto:info@clinic.com">info@clinic.com</a></p>
    </div>
</footer>

</body>
</html>
