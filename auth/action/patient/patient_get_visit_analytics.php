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

$patientId = $_SESSION['patient_id'] ?? null;

if (!$patientId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing patient ID in session']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            visit_id,
            visit_date,
            blood_pressure,
            temperature,
            weight,
            fundal_height,
            fetal_heart_tone,
            fetal_position,
            chief_complaint,
            doctor_note,
            staff_id,
            created_at,
            updated_at
        FROM visit_analytics
        WHERE patient_id = ?
        ORDER BY visit_date DESC, created_at DESC
    ");
    $stmt->execute([$patientId]);
    $visits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($visits) {
        echo json_encode(['status' => 'success', 'data' => $visits]);
    } else {
        echo json_encode(['status' => 'empty', 'message' => 'No visit records found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}