<?php
session_start();
require 'db.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (!$email || !$pass) {
        $error = "Please enter both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@gmail.com')) {
        $error = "Invalid email format. Must be a @gmail.com address.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $email;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link rel="stylesheet" href="css/login.css" />
</head>
<body>
  <form method="POST" action="">
    <h2>User Login</h2>
    <?php if ($error) echo "<p class='error-message'>" . htmlspecialchars($error) . "</p>"; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] == 'account_deleted'): ?>
        <p class='success-message'>Your account has been successfully deleted.</p>
    <?php endif; ?>
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <input type="submit" value="Login" />
    <p style="text-align:center;">New user? <a href="register.php">Register</a></p>
  </form>
</body>
</html>
