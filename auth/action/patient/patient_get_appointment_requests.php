<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once '../../../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'patient') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$patientId = $_SESSION['user_id'] ?? null;

if (!$patientId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing patient ID in session']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            appointment_date,
            appointment_time,
            chief_complaint,
            status,
            doctor_id,
            responded_by,
            response_date,
            created_at,
            updated_at
        FROM appointment_requests
        WHERE patient_id = ?
        ORDER BY appointment_date DESC, appointment_time DESC
    ");
    $stmt->execute([$patientId]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($appointments) {
        echo json_encode(['status' => 'success', 'data' => $appointments]);
    } else {
        echo json_encode(['status' => 'empty', 'message' => 'No appointment requests found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}