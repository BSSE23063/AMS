<?php
require 'db_connect.php';

// Add id column if it doesn't exist
$sql = "SHOW COLUMNS FROM flights LIKE 'id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Add id column
    $sql = "ALTER TABLE flights 
            ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST";
    
    if ($conn->query($sql) === TRUE) {
        echo "ID column added successfully";
    } else {
        echo "Error adding ID column: " . $conn->error;
    }
}

$conn->close();
?>
