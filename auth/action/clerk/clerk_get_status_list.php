<?php
session_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Check session and role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../../config/config.php';

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection not initialized'
    ]);
    exit;
}

try {
    // Ensure status_reference table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS status_reference (
            label_id INT AUTO_INCREMENT PRIMARY KEY,
            status_label VARCHAR(50) UNIQUE NOT NULL,
            description TEXT
        )
    ");

    // Fetch all status labels
    $stmt = $pdo->prepare("SELECT label_id, status_label FROM status_reference ORDER BY status_label ASC");
    $stmt->execute();
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $statuses,
        'message' => count($statuses) ? null : 'No status labels found'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}