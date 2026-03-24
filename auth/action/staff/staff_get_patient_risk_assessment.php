<?php

header('Content-Type: application/json');
require_once '../../../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$patientId = $_SESSION['selectedPatientId'] ?? 0;

if (!$patientId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing patient ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            pra.risk_level,
            pra.remarks,
            pra.assessed_at,
            CONCAT(u.first_name, ' ', u.last_name) AS assessed_by
        FROM patient_risk_assessment pra
        LEFT JOIN users u ON pra.staff_id = u.id
        WHERE pra.patient_id = ?
        ORDER BY pra.assessed_at DESC
        LIMIT 1
    ");
    $stmt->execute([$patientId]);
    $risk = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($risk) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'risk_level'   => $risk['risk_level'],
                'remarks'      => $risk['remarks'],
                'assessed_at'  => $risk['assessed_at'],
                'assessed_by'  => $risk['assessed_by'] ?? 'Unknown'
            ]
        ]);
    } else {
        echo json_encode(['status' => 'empty', 'message' => 'No risk assessment found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}