<?php
require 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // AUTO ROLE CHECK
    if (str_ends_with($email, '@school.com')) {
        $role = 'admin';
    } else {
        $role = 'user';
    }

    $stmt = $pdo->prepare("INSERT INTO ecomm_users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $role]);

    echo "<p style='color:green;'>Registration successful! <a href='login.php'>Login here</a></p>";
}
?>

<!DOCTYPE html>
<html>
<head>
<style>

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

body {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;

    height: 100vh;
    background-size: cover;
    background-position: center;
    animation: slideshow 15s infinite;
}

/* SLIDESHOW BACKGROUND */
@keyframes slideshow {
    0%   { background-image: url('assets/images/bg1.jpg'); }
    33%  { background-image: url('assets/images/bg2.jpg'); }
    66%  { background-image: url('assets/images/bg3.jpg'); }
    100% { background-image: url('assets/images/bg1.jpg'); }
}

/* CENTER CARD */
.register-container {
    width: 360px;
    margin: auto;
    margin-top: 90px;
    padding: 30px;
    border-radius: 12px;

    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
    box-shadow: 0 0 20px rgba(0,0,0,0.3);

    text-align: center;
    color: #fff;
}

.register-container h2 {
    font-size: 26px;
    margin-bottom: 15px;
    font-weight: 600;
}

/* INPUTS */
input[type="text"], input[type="email"], input[type="password"] {
    width: 90%;
    padding: 12px;
    margin-top: 10px;
    margin-bottom: 18px;

    border: none;
    border-radius: 8px;
    outline: none;
    font-size: 14px;
}

/* BUTTON */
button {
    width: 95%;
    padding: 12px;
    background: #4A8CF7;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
    font-weight: 600;
}

button:hover {
    background: #2E6ADF;
}

a {
    color: #fff;
    font-size: 14px;
    display: block;
    margin-top: 10px;
}

</style>
</head>

<body>

<div class="register-container">
    <h2>Create Account</h2>
    <p style="opacity:0.8; font-size:14px;">Join CampusMarket today</p>

    <form method="POST">
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Register</button>
    </form>

    <a href="login.php">Already have an account? Login</a>
</div>

</body>
</html>




