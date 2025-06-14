<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: login.html");
    exit();
}

include 'db.php';

$employee_id = $_SESSION['employee_id'];
$upload_msg = '';

// CSRF token for delete protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle payslip upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payslip'])) {
    $target_dir = "uploads/payslips/{$employee_id}/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_name = basename($_FILES["payslip"]["name"]);
    $file_name = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $file_name);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $_FILES["payslip"]["tmp_name"]);
    finfo_close($finfo);

    $allowed_mimes = ['application/pdf', 'image/jpeg', 'image/png'];
    $max_size = 5 * 1024 * 1024;

    if (!in_array($file_ext, $allowed_types) || !in_array($mime_type, $allowed_mimes)) {
        $upload_msg = "Error: Only PDF, JPG, JPEG, PNG files are allowed.";
    } elseif ($_FILES["payslip"]["size"] > $max_size) {
        $upload_msg = "Error: File size exceeds 5MB.";
    } else {
        $unique_name = time() . "_" . $file_name;
        $target_file = $target_dir . $unique_name;

        if (move_uploaded_file($_FILES["payslip"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO payslips (employee_id, file_name) VALUES (?, ?)");
            $stmt->bind_param("is", $employee_id, $unique_name);
            $stmt->execute();
            $stmt->close();
            $upload_msg = "Payslip uploaded successfully.";
        } else {
            $upload_msg = "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'], $_POST['csrf_token'])) {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $conn->prepare("SELECT file_name FROM payslips WHERE id = ? AND employee_id = ?");
        $stmt->bind_param("ii", $delete_id, $employee_id);
        $stmt->execute();
        $stmt->bind_result($filename);
        if ($stmt->fetch()) {
            $file_path = "uploads/payslips/{$employee_id}/" . $filename;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM payslips WHERE id = ? AND employee_id = ?");
            $stmt->bind_param("ii", $delete_id, $employee_id);
            $stmt->execute();
            $stmt->close();

            $upload_msg = "Payslip deleted successfully.";
        } else {
            $stmt->close();
            $upload_msg = "Payslip not found.";
        }
    } else {
        $upload_msg = "Invalid CSRF token.";
    }
}

// Fetch payslips
$stmt = $conn->prepare("SELECT id, file_name, uploaded_at FROM payslips WHERE employee_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$payslips = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle offer letter upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['offer_letter'])) {
    $target_dir = "uploads/offer_letters/{$employee_id}/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_name = basename($_FILES["offer_letter"]["name"]);
    $file_name = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", $file_name);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $_FILES["offer_letter"]["tmp_name"]);
    finfo_close($finfo);

    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
    $allowed_mimes = ['application/pdf', 'image/jpeg', 'image/png'];
    $max_size = 5 * 1024 * 1024;

    if (!in_array($file_ext, $allowed_types) || !in_array($mime_type, $allowed_mimes)) {
        $upload_msg = "Error: Invalid offer letter format.";
    } elseif ($_FILES["offer_letter"]["size"] > $max_size) {
        $upload_msg = "Error: Offer letter exceeds 5MB.";
    } else {
        $unique_name = time() . "_" . $file_name;
        $target_file = $target_dir . $unique_name;

        if (move_uploaded_file($_FILES["offer_letter"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO offer_letters (employee_id, file_name) VALUES (?, ?)");
            $stmt->bind_param("is", $employee_id, $unique_name);
            $stmt->execute();
            $stmt->close();
            $upload_msg = "Offer letter uploaded successfully.";
        } else {
            $upload_msg = "Error uploading offer letter.";
        }
    }
}

// Fetch offer letters
$stmt = $conn->prepare("SELECT file_name, uploaded_at FROM offer_letters WHERE employee_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$offer_letters = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | Zaltix</title>
    <link rel="icon" href="image logo/favicon.jpg" type="image/png">
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background-color: #2c1c4d; }
        header { background-color: #2c1c4d; padding: 1rem 0; display: flex; justify-content: space-between; align-items: center; color: white;border-bottom: 2px solid #FFDC00;
position: sticky;
top: 0;
z-index: 1000;
width: 100%; }
        header img { height: 80px; }
        nav a { color: white; margin-left: 30px; text-decoration: none; }
        nav a:hover { color: #ffdc00; }
        .container { max-width: 800px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2, h3 { color: #2c1c4d; }
        .message { color: green; margin-bottom: 1rem; }
        input[type="file"] { padding: 10px; margin-bottom: 1rem; width: 100%; }
        input[type="submit"], button { background-color:  #FFDC00; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        input[type="submit"]:hover, button:hover { background-color: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ccc; padding: 0.75rem; text-align: left; }
        th { background-color: #2c1c4d; color: white; }
        td a { color: #007bff; text-decoration: none; }
        td a:hover { text-decoration: underline; }
        .delete-button { background-color: #dc3545; }
        .delete-button:hover { background-color: #c82333; }
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
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['employee_name']); ?>!</h2>

        <?php if ($upload_msg): ?>
            <p class="message"><?php echo htmlspecialchars($upload_msg); ?></p>
        <?php endif; ?>

        <!-- Payslip Section -->
        <h3>Upload Payslip</h3>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="payslip" required>
            <input type="submit" value="Upload">
        </form>

        <h3>Your Payslips</h3>
        <?php if (count($payslips) > 0): ?>
            <table>
                <tr>
                    <th>File Name</th>
                    <th>Uploaded At</th>
                    <th>Download</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($payslips as $ps): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ps['file_name']); ?></td>
                        <td><?php echo htmlspecialchars($ps['uploaded_at']); ?></td>
                        <td><a href="uploads/payslips/<?php echo $employee_id . '/' . rawurlencode($ps['file_name']); ?>" download>Download</a></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this payslip?');" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo $ps['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No payslips uploaded yet.</p>
        <?php endif; ?>

        <!-- Offer Letter Section -->
        <h3>Upload Offer Letter</h3>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="offer_letter" required>
            <input type="submit" value="Upload Offer Letter">
        </form>

        <h3>Your Offer Letters</h3>
        <?php if (count($offer_letters) > 0): ?>
            <table>
                <tr>
                    <th>File Name</th>
                    <th>Uploaded At</th>
                    <th>Download</th>
                </tr>
                <?php foreach ($offer_letters as $ol): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ol['file_name']); ?></td>
                        <td><?php echo htmlspecialchars($ol['uploaded_at']); ?></td>
                        <td><a href="uploads/offer_letters/<?php echo $employee_id . '/' . rawurlencode($ol['file_name']); ?>" download>Download</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No offer letters uploaded yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>