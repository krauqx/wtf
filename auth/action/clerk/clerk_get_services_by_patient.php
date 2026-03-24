<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');

header('Content-Type: application/json');

// Simulate role for direct execution or testing
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'admin'; // fallback role for testing
}

// Clerk/admin access only
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Validate patient_id
$patientId = $_GET['patient_id'] ?? null;
if (!$patientId || !is_numeric($patientId)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid patient ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            cs.service_id,
            cs.service_date,
            cs.service_type,
            cs.service_amount,
            cs.notes,
            CONCAT(u.first_name, ' ', u.last_name) AS doctor_name
        FROM clerk_services cs
        LEFT JOIN users u ON cs.doctor_id = u.id
        WHERE cs.patient_id = ?
        ORDER BY cs.service_date DESC
    ");
    $stmt->execute([$patientId]);
    $services = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $services]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}