<?php
header('Content-Type: application/json');
require __DIR__ . '/db_connection.php';
require __DIR__ . '/auth_check.php';

try {
    $stmt = $pdo->prepare("SELECT full_name, institute FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        echo json_encode([
            'full_name' => $user['full_name'],
            'institute' => $user['institute'] ?? 'No institute specified'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>