<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) { 
    header('Location: login_as_admin.php'); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_SESSION['admin_id'];
    $flight_no = $conn->real_escape_string($_POST['flight_no']);
    $airline = $conn->real_escape_string($_POST['airline']);
    $route = $conn->real_escape_string($_POST['route']);
    $departure = $conn->real_escape_string($_POST['departure']);
    $arrival = $conn->real_escape_string($_POST['arrival']);
    $gate = $conn->real_escape_string($_POST['gate']);
    $status = $conn->real_escape_string($_POST['status']);

    // Basic validation
    if (empty($flight_no) || empty($airline) || empty($route) || 
        empty($departure) || empty($arrival) || empty($gate) || empty($status)) {
        $_SESSION['error'] = "All fields are required!";
        header('Location: homepage.php');
        exit();
    }

    // Check if departure is before arrival
    if (strtotime($departure) >= strtotime($arrival)) {
        $_SESSION['error'] = "Departure time must be before arrival time!";
        header('Location: homepage.php');
        exit();
    }

    $sql = "INSERT INTO flights (admin_id, flight_no, airline, route, departure, arrival, gate, status)
            VALUES ('$admin_id', '$flight_no', '$airline', '$route', '$departure', '$arrival', '$gate', '$status')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Flight added successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }

    $conn->close();
    header('Location: homepage.php');
    exit();
} else {
    header('Location: homepage.php');
    exit();
}
?>