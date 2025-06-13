<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['flight_id'])) {
    $flight_no = $_POST['flight_id'];
    
    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM flights WHERE flight_no = ?");
    $stmt->bind_param("s", $flight_no);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'No flight ID provided']);
}

$conn->close();
?>