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
            tracker_id,
            lmp_date,
            edc_date,
            aog_weeks,
            notes,
            created_at,
            updated_at
        FROM pregnancy_tracker
        WHERE patient_id = ?
        ORDER BY updated_at DESC
        LIMIT 1
    ");
    $stmt->execute([$patientId]);
    $tracker = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tracker) {
        echo json_encode(['status' => 'success', 'data' => $tracker]);
    } else {
        echo json_encode(['status' => 'empty', 'message' => 'No pregnancy tracker record found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}