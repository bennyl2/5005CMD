<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/db_connection.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid data']));
}

try {
    if (empty($input['email']) || empty($input['password'])) {
        http_response_code(400);
        die(json_encode(['error' => 'Email and password required']));
    }

    $stmt = $pdo->prepare("SELECT user_id, password_hash, user_category FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($input['password'], $user['password_hash'])) {
        http_response_code(401);
        die(json_encode(['error' => 'Invalid credentials']));
    }

    // Update last login
    $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?")
        ->execute([$user['user_id']]);

    session_start();
    $_SESSION['user_id'] = $user['user_id'];
    
    // Updated redirect logic
    $redirect = 'dashboard.html'; // Default for students
    if ($user['user_category'] === 'teacher') {
        $redirect = 'teacher_dashboard.html';
    } elseif ($user['user_category'] === 'public-user') {
        $redirect = 'public_dashboard.html';
    }

    echo json_encode(['redirect' => $redirect]);

} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}
?>