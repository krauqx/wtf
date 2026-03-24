<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once '../../../config/config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing patient ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            transaction_id,
            DATE_FORMAT(created_at, '%Y-%m-%d') AS date,
            description,
            amount,
            transaction_type
        FROM billing_transactions
        WHERE patient_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$patientId]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $transactions]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}