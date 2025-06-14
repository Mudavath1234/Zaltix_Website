<?php
// Database credentials for contactdb
$servername = "localhost";
$username = "u337676753_contact_db";  // create a new DB user or use existing one
$password = "Zaltix@contact25";         // set appropriate password
$dbname = "u337676753_contactdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data and sanitize
$name = $conn->real_escape_string($_POST['name']);
$email = $conn->real_escape_string($_POST['email']);
$message = $conn->real_escape_string($_POST['message']);

// Insert query
$sql = "INSERT INTO contacts (name, email, message) VALUES ('$name', '$email', '$message')";

if ($conn->query($sql) === TRUE) {
    // Redirect to thank-you page after successful insert
    header("Location: thankyou.html");
    exit();
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>