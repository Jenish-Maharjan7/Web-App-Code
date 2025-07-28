<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);

    session_destroy();
    header("Location: login.php?message=account_deleted");
    exit();
} catch (PDOException $e) {
    error_log("Error deleting user: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while deleting your account. Please try again.";
    header("Location: dashboard.php");
    exit();
}
?>
