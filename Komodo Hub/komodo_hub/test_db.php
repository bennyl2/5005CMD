[file name]: test_db.php
[file content begin]
<?php
header('Content-Type: application/json');
require __DIR__ . '/db_connection.php';

try {
    // Test connection and tables
    $tables = ['komodo_quiz_results', 'marine_quiz_results'];
    $results = [];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
        $stmt->execute([$table]);
        $results[$table] = $stmt->fetch() ? 'Exists' : 'Missing';
    }
    
    echo json_encode([
        'status' => 'success',
        'tables' => $results,
        'db_path' => $db_path,
        'writable' => is_writable($db_path)
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
[file content end]