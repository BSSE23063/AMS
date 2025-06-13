<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_flight_no = $_POST['flight_id']; // Using flight_id to store old flight number
    $flight_no = $_POST['flight_no'];
    $airline = $_POST['airline'];
    $route = $_POST['route'];
    $departure = $_POST['departure'];
    $arrival = $_POST['arrival'];
    $gate = $_POST['gate'];
    $status = $_POST['status'];
    
    // Prepare update query
    $stmt = $conn->prepare("UPDATE flights SET flight_no = ?, airline = ?, route = ?, departure = ?, arrival = ?, gate = ?, status = ? WHERE flight_no = ?");
    $stmt->bind_param("ssssssss", $flight_no, $airline, $route, $departure, $arrival, $gate, $status, $old_flight_no);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Fetch flight details for editing
    $flight_no = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM flights WHERE flight_no = ?");
    $stmt->bind_param("s", $flight_no);
    $stmt->execute();
    $result = $stmt->get_result();
    $flight = $result->fetch_assoc();
    
    echo json_encode($flight);
    $stmt->close();
}

$conn->close();
?>