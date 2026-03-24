<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');
header('Content-Type: application/json');



// Clerk/admin access only
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Validate patient_id
$patientId = $_GET['patient_id'] ?? null;
if (!$patientId || !is_numeric($patientId)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid patient ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT total_balance
        FROM billing_balance
        WHERE patient_id = ?
        LIMIT 1
    ");
    $stmt->execute([$patientId]);
    $balance = $stmt->fetchColumn();

    echo json_encode([
        'status' => 'success',
        'total_balance' => $balance !== false ? floatval($balance) : 0.00
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}