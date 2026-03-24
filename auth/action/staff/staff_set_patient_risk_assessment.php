<?php

header('Content-Type: application/json');

require_once '../../../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$staffId   = $_SESSION['user_id'];
$patientId = $_SESSION['selectedPatientId'] ?? 0;
$riskLevel = strtolower(trim($_POST['risk_level'] ?? 'not_set'));
$remarks   = trim($_POST['remarks'] ?? '');

if (!$patientId || !in_array($riskLevel, ['not_set', 'low', 'high'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO patient_risk_assessment (patient_id, staff_id, risk_level, remarks)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            risk_level = VALUES(risk_level),
            remarks = VALUES(remarks),
            assessed_at = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$patientId, $staffId, $riskLevel, $remarks]);

    echo json_encode(['status' => 'success', 'message' => 'Risk assessment saved']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}