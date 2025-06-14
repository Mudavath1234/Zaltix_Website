<?php
session_start();
include 'db.php';

if (!isset($_SESSION['employee_id'])) {
    header("Location: login.html");
    exit();
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_SESSION['employee_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM employees WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current_password, $hashed_password)) {
        $msg = "Incorrect current password.";
    } elseif ($new_password !== $confirm_password) {
        $msg = "New passwords do not match.";
    } else {
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE employees SET password = ? WHERE id = ?");
        $update->bind_param("si", $new_hashed, $employee_id);
        if ($update->execute()) {
            $msg = "Password successfully changed.";
        } else {
            $msg = "Error updating password.";
        }
        $update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Change Password | Zaltix</title>
  <link rel="icon" href="image logo/favicon.jpg" type="image/png">
  <style>
    body { margin: 0; font-family: 'Segoe UI', sans-serif; background-color: #2c1c4d; }
    header {
      background-color: #2c1c4d; padding: 1rem 0;
      display: flex; justify-content: space-between; align-items: center;
      color: white; border-bottom: 2px solid #FFDC00;
      position: sticky; top: 0; z-index: 1000; width: 100%;
    }
    header img { height: 80px; }
    nav a { color: white; margin-left: 30px; text-decoration: none; }
    nav a:hover { color: #ffdc00; }

    .container {
      max-width: 500px;
      margin: 2rem auto;
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 { color: #2c1c4d; text-align: center; }
    label { display: block; margin-top: 10px; font-weight: bold; color: #2c1c4d; }

    .password-container {
      display: flex;
      align-items: center;
      width: 100%;
    }

    input[type="password"] {
      width: calc(100% - 40px);
      padding: 0.8rem;
      margin-bottom: 1rem;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .toggle-btn {
      width: 35px; height: 35px; background: #2c1c4d;
      color: white; border: none; border-radius: 50%;
      cursor: pointer; text-align: center; font-size: 16px;
    }

    button[type="submit"] {
      background-color: #FFDC00;
      color: #2c1c4d;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
      font-size: 16px;
    }

    button[type="submit"]:hover {
      background-color: #218838;
      color: white;
    }

    .msg {
      text-align: center;
      font-weight: bold;
      margin-top: 1rem;
      color: <?php echo ($msg === "Password successfully changed.") ? 'green' : 'red'; ?>;
    }
  </style>
</head>
<body>

  <header>
    <a href="index.html"><img src="image logo/Screenshot_30-4-2025_154735_-removebg-preview.png" alt="Zaltix Logo"></a>
    <nav>
      <a href="index.html">Home</a>
      <a href="employee-change-password.php">Change Password</a>
      <a href="add_employee.php">Add Employee</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <div class="container">
    <h2>Change Your Password</h2>

    <form method="POST">
      <label for="current_password">Current Password:</label>
      <div class="password-container">
        <input type="password" id="current_password" name="current_password" required>
        <button type="button" class="toggle-btn" onclick="togglePassword('current_password')">👁</button>
      </div>

      <label for="new_password">New Password:</label>
      <div class="password-container">
        <input type="password" id="new_password" name="new_password" required>
        <button type="button" class="toggle-btn" onclick="togglePassword('new_password')">👁</button>
      </div>

      <label for="confirm_password">Confirm New Password:</label>
      <div class="password-container">
        <input type="password" id="confirm_password" name="confirm_password" required>
        <button type="button" class="toggle-btn" onclick="togglePassword('confirm_password')">👁</button>
      </div>

      <button type="submit">Change Password</button>

      <?php if ($msg): ?>
        <p class="msg"><?php echo htmlspecialchars($msg); ?></p>
      <?php endif; ?>
    </form>
  </div>

  <script>
  function togglePassword(fieldId) {
      var field = document.getElementById(fieldId);
      field.type = field.type === "password" ? "text" : "password";
  }
  </script>
</body>
</html>
