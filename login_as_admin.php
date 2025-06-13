<?php
session_start();
require 'db_connect.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, trim($_POST['username'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Username or password cannot be empty.';
        header('Location: login_as_admin.php');
        exit();
    }

    $sql = "SELECT * FROM admins WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
          if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['name'];
            
            // Archive completed flights
            require_once 'archive_flights.php';
            archiveCompletedFlights($conn);
            archiveCompletedFlights($conn);
            
            header('Location: homepage.php');
            exit();
        } else {
            $_SESSION['error'] = 'Incorrect password. Please try again.';
        }
    } else {
        $_SESSION['error'] = 'Admin not found. Please check your credentials.';
    }
    
    header('Location: login_as_admin.php');
    exit();
}
