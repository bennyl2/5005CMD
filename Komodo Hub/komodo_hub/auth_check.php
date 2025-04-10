<?php
session_start();
require __DIR__ . '/db_connection.php';

if (empty($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    die(json_encode(['error' => 'Not authenticated']));
}

// Optionally fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('HTTP/1.1 401 Unauthorized');
    die(json_encode(['error' => 'User not found']));
}
?>