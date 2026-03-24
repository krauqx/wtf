<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');

header('Content-Type: application/json');

// Ensure only clerks can access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Optional: allow filtering by role (default to 'doctor')
$role = $_GET['role'] ?? 'staff';

try {
    $stmt = $pdo->prepare("
        SELECT id, CONCAT(first_name, ' ', last_name) AS full_name
        FROM users
        WHERE role = ?
    ");
    $stmt->execute([$role]);
    $staffList = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $staffList]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}