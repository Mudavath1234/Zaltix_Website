<?php
$servername = "localhost";
$username = "u337676753_employees_db";
$password = "Zaltixemploye@2K25";
$dbname = "u337676753_employe_portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>