<?php
// db_connect.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skyport_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create flight_archives table if it doesn't exist
$archiveTableSql = "CREATE TABLE IF NOT EXISTS flight_archives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_no VARCHAR(20) NOT NULL,
    airline VARCHAR(100) NOT NULL,
    route VARCHAR(100) NOT NULL,
    departure DATETIME NOT NULL,
    arrival DATETIME NOT NULL,
    gate VARCHAR(10) NOT NULL,
    status VARCHAR(20) NOT NULL,
    archived_at DATETIME NOT NULL,
    archive_reason VARCHAR(100) NOT NULL
)";

if ($conn->query($archiveTableSql) === FALSE) {
    die('Error creating archive table: ' . $conn->error);
}
?>