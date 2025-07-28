<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>EasyEvent Planner - Home</title>
  <link rel="stylesheet" href="css/home.css" />
</head>
<body>
    <header>
        <h1>Welcome to EasyEvent Planner!</h1>
    </header>
    <nav>
        <a href="home.php">Home</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
        <?php
        session_start();
        if (isset($_SESSION['user_id'])) {
            echo '<a href="dashboard.php">Dashboard</a>';
            echo '<a href="my_events.php">My Events</a>';
            echo '<a href="home.php?logout=1">Logout</a>';
        }
        ?>
    </nav>
    <main>
        <h2>Your ultimate event planning companion</h2>
        <p>EasyEvent Planner helps you organize meetings, weddings, parties, and conferences with ease. Plan your event right now!</p>
        <p>Get started by <a href="register.php">registering</a> or <a href="login.php">logging in</a>!</p>
    </main>
    <footer>
        <p>&copy; 2025 EasyEvent Planner</p>
    </footer>
    <?php
    if (isset($_GET['logout']) && $_GET['logout'] == '1') {
        session_unset();
        session_destroy();
        header("Location: home.php");
        exit();
    }
    ?>
</body>
</html>
