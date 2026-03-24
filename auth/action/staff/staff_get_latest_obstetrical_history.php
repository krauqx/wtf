<?php

session_start();

require_once '../../../config/config.php';

header('Content-Type: application/json');

$patientId = $_GET['patient_id'] ?? null;

if (!$patientId || !is_numeric($patientId)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid patient ID'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM obstetrical_history
        WHERE patient_id = ?
        ORDER BY visit_date DESC, created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$patientId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        echo json_encode([
            'status' => 'success',
            'data' => $record
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'data' => null
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}