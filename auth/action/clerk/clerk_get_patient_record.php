<?php
session_start();

// Suppress HTML errors, log instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Check session and role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../../../config/config.php';

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection not initialized'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$targetPatientId = $_GET['patient_id'] ?? null;

if (!$targetPatientId || !is_numeric($targetPatientId)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing or invalid patient_id'
    ]);
    exit;
}

try {
    // Step 1: Verify patient exists
    $stmt = $pdo->prepare("SELECT * FROM patient_records WHERE patient_id = ?");
    $stmt->execute([$targetPatientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No patient record found'
        ]);
        exit;
    }

    // Step 2: Get latest patient status
    $statusSql = "
        SELECT sr.status_label, ps.updated_at
        FROM patient_status ps
        JOIN status_reference sr ON ps.status_label_id = sr.label_id
        WHERE ps.patient_id = ?
        ORDER BY ps.updated_at DESC
        LIMIT 1
    ";
    $stmt = $pdo->prepare($statusSql);
    $stmt->execute([$targetPatientId]);
    $statusRow = $stmt->fetch(PDO::FETCH_ASSOC);

    $statusLabel = $statusRow['status_label'] ?? 'No Status';
    $statusTime = $statusRow['updated_at'] ?? null;

    // Step 3: Return patient record with status
    echo json_encode([
        'status' => 'success',
        'data' => [
            'patient' => $patient,
            'status_label' => $statusLabel,
            'status_updated_at' => $statusTime
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}