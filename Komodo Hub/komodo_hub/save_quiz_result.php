[file name]: save_quiz_result.php
[file content begin]
<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require __DIR__ . '/db_connection.php';
require __DIR__ . '/auth_check.php';

session_start();

// Debugging
file_put_contents('quiz_debug.log', "[" . date('Y-m-d H:i:s') . "] Starting save_quiz_result.php\n", FILE_APPEND);

try {
    $input = json_decode(file_get_contents('php://input'), true);
    file_put_contents('quiz_debug.log', "[" . date('Y-m-d H:i:s') . "] Input: " . print_r($input, true) . "\n", FILE_APPEND);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    $required = ['quiz_type', 'score', 'time_taken'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Verify user session
    if (empty($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Get user details
    $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('User not found in database');
    }

    // Determine table
    $validTypes = ['komodo', 'marine'];
    if (!in_array($input['quiz_type'], $validTypes)) {
        throw new Exception('Invalid quiz type');
    }
    $table = $input['quiz_type'] . '_quiz_results';

    // Insert result
    $stmt = $pdo->prepare("
        INSERT INTO $table (user_id, user_name, score, time_taken)
        VALUES (?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        $user['user_id'],
        $user['full_name'],
        $input['score'],
        $input['time_taken']
    ]);

    if (!$success) {
        throw new Exception('Failed to insert quiz result');
    }

    $result = [
        'success' => true,
        'inserted_id' => $pdo->lastInsertId()
    ];

    file_put_contents('quiz_debug.log', "[" . date('Y-m-d H:i:s') . "] Success: " . print_r($result, true) . "\n", FILE_APPEND);
    echo json_encode($result);

} catch (Exception $e) {
    $error = [
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ];
    
    file_put_contents('quiz_debug.log', "[" . date('Y-m-d H:i:s') . "] Error: " . print_r($error, true) . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode($error);
}
?>
[file content end]
