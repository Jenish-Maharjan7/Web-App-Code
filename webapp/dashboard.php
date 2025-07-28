<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: home.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (preg_match('/\d/', $name)) {
        $error = "Name cannot contain numbers.";
    } else {
        $sql = "UPDATE users SET name = :name, phone = :phone WHERE id = :id";
        $params = ['name' => $name, 'phone' => $phone, 'id' => $user_id];

        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $paths = 'uploads/';
            if (!is_dir($paths)) {
                mkdir($paths, 0777, true);
            }
            $filename = time() . '_' . basename($_FILES['profile_photo']['name']);
            $targetFile = $paths . $filename;

            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFile)) {
                $sql = "UPDATE users SET name = :name, phone = :phone, profile_photo = :profile_photo WHERE id = :id";
                $params['profile_photo'] = $targetFile;
            } else {
                $error = "Failed to upload profile photo.";
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $success = "Profile updated successfully.";
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->execute(['id' => $user_id]);
                $user = $stmt->fetch();
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = "Profile updated successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
  <div class="container">
    <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>

    <?php if (!empty($user['profile_photo'])): ?>
      <img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile Photo" class="profile-photo" />
    <?php endif; ?>

    <?php if ($success): ?>
    <p class="success-message"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
    <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <label>Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required />

      <label>Email (read-only)</label>
      <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly />

      <label>Phone Number</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" />

      <label>Profile Photo</label>
      <input type="file" name="profile_photo" accept="image/*" />

      <input type="submit" value="Update Profile" />
    </form>

    <form method="POST" action="delete.php" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');" style="margin-top:20px;">
        <button type="submit" class="nav-button delete">Delete Account</button>
    </form>

    <div class="nav-container">
        <a href="my_events.php" class="nav-button">My Events</a>
        <a href="home.php?logout=1" class="nav-button logout">Logout</a>
    </div>
  </div>
</body>
</html>
