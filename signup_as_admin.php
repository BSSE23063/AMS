<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "skyport_db";


// Create connection
$conn = new mysqli($servername, $username, $password);



// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Create the database if it doesn't exist
$dbSql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($dbSql) === FALSE) {
    die('Error creating database: ' . $conn->error);
}

$conn = new mysqli($servername, $username, $password, $database);
// Select the database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->select_db($database);
// Create the admins table if it doesn't exist
$tableSql = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL
)";

if ($conn->query($tableSql) === FALSE) {
    die('Error creating table: ' . $conn->error);
}

// Create flight_archives table
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
// Capture form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert into database
    $sql = "INSERT INTO admins (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);    if ($stmt->execute()) {
        // Archive completed flights
        require_once 'archive_flights.php';
        archiveCompletedFlights($conn);
        archiveCompletedFlights($conn);
        
        header("Location: login.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
