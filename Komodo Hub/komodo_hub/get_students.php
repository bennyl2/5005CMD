<?php
header('Content-Type: application/json');
require __DIR__ . '/db_connection.php';
require __DIR__ . '/auth_check.php';

try {
    // This should be modified to fetch the teacher's actual students
    // Currently just fetching all students as an example
    $stmt = $pdo->prepare("SELECT user_id, full_name, email FROM users WHERE user_category = 'student'");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($students);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>