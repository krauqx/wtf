<?php
session_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Only allow staff role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../../config/config.php';

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection not initialized']);
    exit;
}

$patientId = $_GET['patient_id'] ?? null;
if (!$patientId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing patient_id parameter']);
    exit;
}

try {
    $sql = "
        SELECT *
        FROM medical_history
        WHERE patient_id = :pid
        ORDER BY visit_date DESC, history_id DESC
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pid' => $patientId]);
    $latest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($latest) {
        echo json_encode(['status' => 'success', 'data' => $latest]);
    } else {
        echo json_encode(['status' => 'empty', 'message' => 'No medical history found for this patient']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO error in staff_get_latest_medical_history.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}