<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = $_GET['id'] ?? null;
$error = "";
$success = "";
$event = null;

if ($event_id) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id AND user_id = :user_id");
    $stmt->execute(['event_id' => $event_id, 'user_id' => $user_id]);
    $event = $stmt->fetch();

    if (!$event) {
        $error = "Event not found or you don't have permission to edit it.";
        $event_id = null;
    }
} else {
    $error = "No event ID provided.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event_id) {
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
            $stmt = $pdo->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date, event_time = :event_time, location = :location, description = :description, is_public = :is_public WHERE event_id = :event_id AND user_id = :user_id");
            $stmt->execute([
                'event_name' => $event_name,
                'event_date' => $event_date,
                'event_time' => !empty($event_time) ? $event_time : null,
                'location' => $location,
                'description' => $description,
                'is_public' => $is_public,
                'event_id' => $event_id,
                'user_id' => $user_id
            ]);
            $success = "Event updated successfully!";
            $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id AND user_id = :user_id");
            $stmt->execute(['event_id' => $event_id, 'user_id' => $user_id]);
            $event = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Error updating event: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <link rel="stylesheet" href="css/events.css">
</head>
<body>
    <div class="container">
        <h2>Edit Event</h2>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($event): ?>
            <form method="POST" action="edit_event.php?id=<?= htmlspecialchars($event_id) ?>">
                <label for="event_name">Event Name*</label>
                <input type="text" id="event_name" name="event_name" value="<?= htmlspecialchars($event['event_name']) ?>" required>

                <label for="event_date">Event Date*</label>
                <input type="date" id="event_date" name="event_date" value="<?= htmlspecialchars($event['event_date']) ?>" required>

                <label for="event_time">Event Time</label>
                <input type="time" id="event_time" name="event_time" value="<?= htmlspecialchars($event['event_time'] ?? '') ?>">

                <label for="location">Location*</label>
                <input type="text" id="location" name="location" value="<?= htmlspecialchars($event['location']) ?>" required>

                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>

                <label for="is_public">
                    <input type="checkbox" id="is_public" name="is_public" value="1" <?= $event['is_public'] ? 'checked' : '' ?>> Make Public
                </label>

                <button type="submit">Update Event</button>
            </form>
        <?php endif; ?>

        <div class="nav-container">
            <a href="my_events.php" class="nav-button">Back to My Events</a>
            <a href="dashboard.php" class="nav-button">Dashboard</a>
        </div>
    </div>
</body>
</html>
