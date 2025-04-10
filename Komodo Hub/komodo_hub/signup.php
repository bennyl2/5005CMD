<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/db_connection.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid JSON data']));
}

try {
    $required = ['name', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            die(json_encode(['error' => "$field is required"]));
        }
    }

    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        die(json_encode(['error' => 'Invalid email format']));
    }

    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        die(json_encode(['error' => 'Email already exists']));
    }

    $stmt = $pdo->prepare("
        INSERT INTO users 
        (full_name, email, password_hash, contact_number, institute, user_category) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $input['name'],
        $input['email'],
        password_hash($input['password'], PASSWORD_BCRYPT),
        $input['contact'] ?? null,
        $input['institute'] ?? null,
        $input['category'] ?? 'student'
    ]);

    if ($success) {
        session_start();
        $_SESSION['user_id'] = $pdo->lastInsertId();
        
        // Updated redirect logic
        $redirect = 'dashboard.html'; // Default for students
        if ($input['category'] === 'teacher') {
            $redirect = 'teacher_dashboard.html';
        } elseif ($input['category'] === 'public-user') {
            $redirect = 'public_dashboard.html';
        }
        
        echo json_encode([
            'redirect' => $redirect,
            'user_id' => $pdo->lastInsertId()
        ]);
    } else {
        throw new Exception("Insert failed");
    }
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'error' => 'Database error',
        'details' => $e->getMessage()
    ]));
}
?>