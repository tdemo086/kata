<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'user_management');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to create the database and table if they don't exist
$sql = "CREATE DATABASE IF NOT EXISTS user_management";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_banned BOOLEAN DEFAULT FALSE
)";
$conn->query($sql);

// Registration
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
    if ($conn->query($query)) {
        $_SESSION['message'] = 'Registration successful! You can now login.';
    } else {
        $_SESSION['message'] = 'Error: ' . $conn->error;
    }
}

// Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            if ($user['is_banned']) {
                $_SESSION['message'] = 'You have been banned.';
            } else {
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['username'] === 'admin'; // Check if the user is an admin
                if ($_SESSION['is_admin']) {
                    header('Location: admin.php'); // Redirect to the admin panel
                } else {
                    header('Location: home.php'); // Redirect to the user homepage
                }
                exit();
            }
        } else {
            $_SESSION['message'] = 'Invalid password.';
        }
    } else {
        $_SESSION['message'] = 'No user found with that username.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register Example with PHP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if(isset($_SESSION['message'])): ?>
            <div class="message"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <h2>Login</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <p style="text-align:center;">Or</p>
        <button onclick="document.getElementById('registerContainer').style.display='block';">Register</button>
    </div>

    <div class="container" id="registerContainer" style="display:none;">
        <h2>Register</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Register</button>
        </form>
        <button onclick="document.getElementById('registerContainer').style.display='none';">Back to Login</button>
    </div>
</body>
</html>
