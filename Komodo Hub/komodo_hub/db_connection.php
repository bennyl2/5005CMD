<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$db_path = __DIR__ . '/komodo_hub.db';

// Verify database is writable
if (!is_writable($db_path) && file_exists($db_path)) {
    die(json_encode(['error' => 'Database file is not writable']));
}

try {
    $pdo = new PDO("sqlite:$db_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = ON");
} catch (PDOException $e) {
    die(json_encode([
        'error' => 'Database connection failed',
        'details' => $e->getMessage(),
        'db_path' => $db_path
    ]));
}
?>