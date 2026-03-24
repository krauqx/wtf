<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../../config/config.php';

$appointmentId = $_POST['appointment_id'] ?? null;
$status = $_POST['status'] ?? 'approved';
$clerkId = $_SESSION['user_id'];

if (!$appointmentId || !is_numeric($appointmentId)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid appointment_id']);
    exit;
}

if (!in_array($status, ['approved', 'reschedule'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE appointment_requests
        SET 
            status = ?,
            responded_by = ?,
            response_date = NOW()
        WHERE id = ? AND status = 'pending'
    ");
    $stmt->execute([$status, $clerkId, $appointmentId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Appointment status updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Appointment not found or already processed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
