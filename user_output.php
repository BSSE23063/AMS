<?php
// Step 1: Connect to the database
$host = 'localhost';
$user = 'root';
$password = ''; // Your DB password
$dbname = 'skyport_db';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Fetch user name (e.g., first user)
$sql = "SELECT name FROM users LIMIT 1";
$result = $conn->query($sql);

// Step 3: If user found, write the name into a file
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $username = $row['name'];

    // Step 4: Write the name into a PHP file
    $fileContent = "<?php\n\$username = '$username';\necho \"User: \$username\";\n?>";

    file_put_contents('user_output.php', $fileContent);

    echo "Username written to user_output.php";
} else {
    echo "No user found.";
}

$conn->close();
?>
