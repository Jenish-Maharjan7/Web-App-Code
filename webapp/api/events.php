<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = :user_id ORDER BY event_date DESC, event_time DESC");
        $stmt->execute(['user_id' => $user_id]);
        $events = $stmt->fetchAll();
        echo json_encode($events);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $event_id = $_POST['event_id'] ?? null;

        if (!$event_id) {
            echo json_encode(['success' => false, 'message' => 'Event ID is required for deletion.']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :event_id AND user_id = :user_id");
            $stmt->execute(['event_id' => $event_id, 'user_id' => $user_id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Event deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Event not found or you do not have permission to delete it.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unsupported request method.']);
}
?>
