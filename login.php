<?php
session_start();
include 'db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM employees WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $user = $result->fetch_assoc();
  if (password_verify($password, $user['password'])) {
    $_SESSION['employee_id'] = $user['id'];
    $_SESSION['employee_name'] = $user['name'];
    header("Location: employee-dashboard.php");
    exit();
  } else {
    echo "Incorrect password.";
  }
} else {
  echo "Employee not found.";
}
?>