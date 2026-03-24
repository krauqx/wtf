<?php
session_start();

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
    echo json_encode(['status' => 'error', 'message' => 'Database connection not initialized']);
    exit;
}

// Validate patient_id
$patientId = $_GET['patient_id'] ?? null;
if (!$patientId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing patient_id parameter']);
    exit;
}

try {
    $sql = "
        SELECT 
            service_id,
            patient_id,
            doctor_id,
            service_date,
            service_type,
            service_amount,
            notes,
            created_at,
            updated_at
        FROM clerk_services
        WHERE patient_id = :pid
        ORDER BY service_date DESC, service_id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pid' => $patientId]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($services && count($services) > 0) {
        echo json_encode([
            'status' => 'success',
            'data' => $services
        ]);
    } else {
        echo json_encode([
            'status' => 'empty',
            'message' => 'No services found for this patient'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO error in clerk_get_patient_services.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}