<?php
// Start session at the beginning
session_start();

// Base path configuration - sesuaikan dengan struktur folder Anda
define('BASE_PATH', '/todo-app/');  // Ganti dengan path aplikasi Anda

// Database configuration
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
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to clean input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Simple router
$request = $_SERVER['REQUEST_URI'];
$request = str_replace(BASE_PATH, '', $request);  // Remove base path
$request = strtok($request, '?');  // Remove query string

// Route the request
switch ($request) {
    case '':
    case '/':
        if (isLoggedIn()) {
            header("Location: " . BASE_PATH . "dashboard.php");
        } else {
            header("Location: " . BASE_PATH . "auth/login.php");
        }
        exit();
        break;

    case 'dashboard.php':
        if (!isLoggedIn()) {
            header("Location: " . BASE_PATH . "auth/login.php");
            exit();
        }
        require __DIR__ . '/dashboard.php';
        break;

    case 'auth/login.php':
        if (isLoggedIn()) {
            header("Location: " . BASE_PATH . "dashboard.php");
            exit();
        }
        require __DIR__ . '/auth/login.php';
        break;

    case 'auth/register.php':
        if (isLoggedIn()) {
            header("Location: " . BASE_PATH . "dashboard.php");
            exit();
        }
        require __DIR__ . '/auth/register.php';
        break;

    case 'auth/logout.php':
        session_destroy();
        header("Location: " . BASE_PATH . "auth/login.php");
        exit();
        break;

    case 'todo/list_handler.php':
        if (!isLoggedIn()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
        require __DIR__ . '/todo/list_handler.php';
        break;

    case 'todo/task_handler.php':
        if (!isLoggedIn()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
        require __DIR__ . '/todo/task_handler.php';
        break;

    case 'profile.php':
        if (!isLoggedIn()) {
            header("Location: " . BASE_PATH . "auth/login.php");
            exit();
        }
        require __DIR__ . '/profile.php';
        break;

    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
?>