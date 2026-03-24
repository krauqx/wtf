<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['patient_id']) || !is_numeric($_SESSION['patient_id'])) {
    echo json_encode([]);
    exit;
}

$patientId = (int) $_SESSION['patient_id'];

try {
    $stmt = $pdo->prepare("
    SELECT visit_date, blood_pressure AS bp, temperature AS temp, weight,
           fundal_height, fetal_heart_tone, fetal_position, chief_complaint
    FROM visit_analytics
    WHERE patient_id = ?
    ORDER BY visit_date DESC
");

    $stmt->execute([$patientId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (PDOException $e) {
    echo json_encode([]);
}