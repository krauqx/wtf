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
$date = $_POST['date'] ?? null;
$time = $_POST['time'] ?? null;
$clerkId = $_SESSION['user_id'];

if (!$appointmentId || !is_numeric($appointmentId)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid appointment_id']);
    exit;
}
if (!$date || !$time) {
    echo json_encode(['status' => 'error', 'message' => 'Missing date or time']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE appointment_requests
        SET 
            appointment_date = ?,
            appointment_time = ?,
            status = 'reschedule',
            responded_by = ?,
            response_date = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$date, $time, $clerkId, $appointmentId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Appointment rescheduled']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Appointment not found or no changes made']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
