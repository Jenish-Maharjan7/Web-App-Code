<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = trim($_POST['event_name'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    $event_time = trim($_POST['event_time'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    if (empty($event_name) || empty($event_date) || empty($location)) {
        $error = "Event Name, Date, and Location are required.";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $event_date)) {
        $error = "Invalid date format. Please use YYYY-MM-DD.";
    } elseif (!empty($event_time) && !preg_match("/^\d{2}:\d{2}$/", $event_time)) {
        $error = "Invalid time format. Please use HH:MM.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO events (user_id, event_name, event_date, event_time, location, description, is_public) VALUES (:user_id, :event_name, :event_date, :event_time, :location, :description, :is_public)");
            $stmt->execute([
                'user_id' => $user_id,
                'event_name' => $event_name,
                'event_date' => $event_date,
                'event_time' => !empty($event_time) ? $event_time : null,
                'location' => $location,
                'description' => $description,
                'is_public' => $is_public
            ]);
            $success = "Event created successfully! <a href='my_events.php'>View My Events</a>";
        } catch (PDOException $e) {
            $error = "Error creating event: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Event</title>
    <link rel="stylesheet" href="css/events.css">
</head>
<body>
    <div class="container">
        <h2>Create New Event</h2>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" action="create_event.php">
            <label for="event_name">Event Name*</label>
            <input type="text" id="event_name" name="event_name" required>

            <label for="event_date">Event Date*</label>
            <input type="date" id="event_date" name="event_date" required>

            <label for="event_time">Event Time</label>
            <input type="time" id="event_time" name="event_time">

            <label for="location">Location*</label>
            <input type="text" id="location" name="location" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"></textarea>

            <label for="is_public">
                <input type="checkbox" id="is_public" name="is_public" value="1" checked> Make Public
            </label>

            <button type="submit">Create Event</button>
        </form>

        <div class="nav-container">
            <a href="my_events.php" class="nav-button">Back to My Events</a>
            <a href="dashboard.php" class="nav-button">Dashboard</a>
        </div>
    </div>
</body>
</html>
