<?php
require 'db.php';
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (preg_match('/\d/', $name)) {
        $error = "Name cannot contain numbers.";
    } elseif (strlen($password) < 8 || !preg_match('/[\W]/', $password)) {
        $error = "Password must be at least 8 characters and include at least one special character.";
    } elseif (!$name || !$email || !$password || !$confirm_password) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@gmail.com')) {
        $error = "Invalid email format. Must be a @gmail.com address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$name, $email, $phone, $hashed_password]);
            if ($result) {
                $success = "Registration successful! <a href='login.php'>Click here to login</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
    <title>User Registration</title>
    <link rel="stylesheet" href="css/register.css" />
</head>
<body>

<div class="container">
    <h2>Register New User</h2>

    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success-message"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>
        <label>Name*</label>
        <input type="text" name="name" required />

        <label>Email*</label>
        <input type="email" name="email" required />

        <label>Phone</label>
        <input type="text" name="phone" />

        <label>Password*</label>
        <input type="password" name="password" required />

        <label>Confirm Password*</label>
        <input type="password" name="confirm_password" required />

        <button type="submit">Register</button>
    </form>
    <p>Already registered? <a href="login.php">Login here</a>.</p>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const name = this.name.value.trim();
    const email = this.email.value.trim();
    const password = this.password.value;
    const confirmPassword = this.confirm_password.value;

    if (/\d/.test(name)) {
        alert("Name cannot contain numbers.");
        e.preventDefault();
        return;
    }
    if (!email.endsWith('@gmail.com')) {
        alert("Email must be a @gmail.com address.");
        e.preventDefault();
        return;
    }
    if (password.length < 8 || !/[\W]/.test(password)) {
        alert("Password must be at least 8 characters and include at least one special character.");
        e.preventDefault();
        return;
    }
    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        e.preventDefault();
        return;
    }
});
</script>
</body>
</html>
