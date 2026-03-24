<?php
require_once __DIR__ . '/../../../config/config.php';
header('Content-Type: application/json');

// Read and decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
$appointment_id = intval($data['appointment_id'] ?? 0);
$status = trim($data['status'] ?? '');

if (!$appointment_id || $status === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Missing or invalid appointment ID or status.'
    ]);
    exit;
}

// Optional: whitelist allowed statuses
$allowedStatuses = ['Waiting', 'Ongoing', 'Done'];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status value.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE appointment_requests
        SET status = :status
        WHERE id = :id
    ");
    $stmt->execute([
        ':status' => $status,
        ':id' => $appointment_id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully.'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}