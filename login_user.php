<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'skyport_db');

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if username and password are set
    $name = isset($_POST['username']) ? mysqli_real_escape_string($conn, trim($_POST['username'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($name) || empty($password)) {
        echo 'Username or password cannot be empty.';
    } else {
        // Query to get the user by username
        $sql = "SELECT * FROM users WHERE name = '$name'";
        $result = $conn->query($sql);

        // Check if the user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password using password_verify
            if (password_verify($password, $user['password'])) {
                // Redirect to homepage if password matches
                header('Location: homepage_USER.html');
                exit();
            } else {
                // Incorrect password
                echo 'Incorrect password. Please try again.';
            }
        } else {
            // User not found
            echo 'User not found. Please sign up first.';
        }
    }
}

$conn->close();
?>
