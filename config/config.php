# File: config/config.php
<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'todo_app');
define('DB_USER', 'root');  // Sesuaikan dengan username database Anda
define('DB_PASS', '');      // Sesuaikan dengan password database Anda

// Function to connect to database
function connectDB() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Function to check if user is logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /todo-app/auth/login.php");
        exit();
    }
}

// Function to clean input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}