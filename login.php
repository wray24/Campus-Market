<?php
session_start();
require 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM ecomm_users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user){
        // Check password
        if(password_verify($password, $user['password_hash'])){

            // Check if suspended
            if(!$user['is_active']){
                $error = "Your account has been suspended. Contact admin.";
            } else {

                // SET SESSION
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                // ROLE-BASED REDIRECT
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            }

        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Invalid email or password!";
    }
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

@keyframes slideshow {
    0%   { background-image: url('assets/images/bg1.jpg'); }
    33%  { background-image: url('assets/images/bg2.jpg'); }
    66%  { background-image: url('assets/images/bg3.jpg'); }
    100% { background-image: url('assets/images/bg1.jpg'); }
}

.login-container {
    width: 350px;
    margin: auto;
    margin-top: 100px;
    padding: 30px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.3);
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
    text-align: center;
    color: #fff;
}

.login-container h2 {
    font-size: 26px;
    margin-bottom: 15px;
    font-weight: 600;
}

input[type="email"], input[type="password"] {
    width: 90%;
    padding: 12px;
    margin-top: 10px;
    margin-bottom: 20px;
    border: none;
    border-radius: 8px;
    outline: none;
    font-size: 14px;
}

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

<div class="login-container">
    <h2>Welcome Back</h2>
    <p style="opacity:0.8; font-size:14px;">Login to continue</p>

    <?php if(isset($error)) echo "<p style='color:#ffdddd; font-weight:500;'>$error</p>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Log In</button>
    </form>

    <a href="register.php">Don't have an account? Register</a>
</div>

</body>
</html>



