<!DOCTYPE html>
<html>
<head>
  <title>Add New Employee</title>
  <style>
    body { font-family: Arial; padding: 2rem; background: #f4f4f4; }
    form {
      background: white; padding: 2rem; border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      max-width: 400px; margin: auto;
    }
    label { display: block; margin-top: 10px; font-weight: bold; }
    .password-container { display: flex; align-items: center; width: 100%; }
    input { width: calc(100% - 40px); padding: 0.8rem; margin-bottom: 1rem; border-radius: 5px; border: 1px solid #ccc; }
    .toggle-btn { width: 35px; height: 35px; background: black; color: white; border: none; border-radius: 50%; cursor: pointer; text-align: center; font-size: 16px; }
    input[type="submit"] { background-color: #28a745; color: white; border: none; cursor: pointer; }
    input[type="submit"]:hover { background-color: #218838; }
  </style>
</head>
<body>
<h2 style="text-align:center;">Add New Employee</h2>

<form method="POST" action="">
  <label for="username">Username:</label>
  <input type="text" name="username" placeholder="Username" required />

  <label for="name">Full Name:</label>
  <input type="text" name="name" placeholder="Full Name" required />

  <label for="password">Password:</label>
  <div class="password-container">
    <input type="password" id="password" name="password" placeholder="Password" required>
    <button type="button" class="toggle-btn" onclick="togglePassword('password')">👁</button>
  </div>

  <input type="submit" name="submit" value="Add Employee" />
</form>

<script>
function togglePassword(fieldId) {
    var field = document.getElementById(fieldId);
    field.type = field.type === "password" ? "text" : "password";
}
</script>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
  include 'db.php';

  $username = trim($_POST['username']);
  $name = trim($_POST['name']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $check = $conn->prepare("SELECT id FROM employees WHERE username = ?");
  $check->bind_param("s", $username);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    echo "<p style='color:red; text-align:center;'>Username already exists.</p>";
  } else {
    $stmt = $conn->prepare("INSERT INTO employees (username, name, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $name, $password);
    if ($stmt->execute()) {
      echo "<p style='color:green; text-align:center;'>Employee added successfully!</p>";
    } else {
      echo "<p style='color:red; text-align:center;'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
  }

  $check->close();
  $conn->close();
}
?>
</body>
</html>
