<?php
session_start();

// Database connection
$conn = new mysqli('database-1.cp0wuao8ky1o.ap-south-1.rds.amazonaws.com', 'admin', 'prachi11', 'image_app');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']); // Prevent SQL Injection
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($query);

    if ($result) { // Check if the query was successful
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: upload.php');
                exit(); // Always exit after header redirection
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found.";
        }
    } else {
        // Log the SQL error for debugging
        echo "Query failed: " . $conn->error;
    }
}

$conn->close(); // Close the database connection
?>

<!-- Login Form -->
<form method="POST">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>
