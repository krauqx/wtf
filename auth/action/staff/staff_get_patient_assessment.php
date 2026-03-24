<?php
session_start();
require_once '../../../config/config.php';

header('Content-Type: application/json');

$patientId = $_SESSION['selectedPatientId'] ?? 0;
$date = $_GET['date'] ?? null;

if (!$patientId || !$date) {
    echo json_encode(['status' => 'error', 'message' => 'Missing patient ID or date']);
    exit;
}

function fetchAssessment(PDO $pdo, int $patientId, string $date): array {
    $data = [];

    // Physical Examination
    $stmt = $pdo->prepare("SELECT * FROM physical_examination_record WHERE patient_id = :id AND DATE(exam_date) = :date LIMIT 1");
    $stmt->execute(['id' => $patientId, 'date' => $date]);
    $data['physicalExamination'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // Pelvic Examination (assumed part of physical)
    $data['pelvicExamination'] = $data['physicalExamination']; // adjust if stored separately

    // Medical History
    $stmt = $pdo->prepare("SELECT * FROM medical_history WHERE patient_id = :id AND DATE(visit_date) = :date LIMIT 1");
    $stmt->execute(['id' => $patientId, 'date' => $date]);
    $data['medicalHistory'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // Obstetrical History
    $stmt = $pdo->prepare("SELECT * FROM obstetrical_history WHERE patient_id = :id AND DATE(visit_date) = :date LIMIT 1");
    $stmt->execute(['id' => $patientId, 'date' => $date]);
    $data['obstetricalHistory'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // VAW Risk
    $stmt = $pdo->prepare("SELECT * FROM vaw_risk_assessment WHERE patient_id = :id AND DATE(visit_date) = :date LIMIT 1");
    $stmt->execute(['id' => $patientId, 'date' => $date]);
    $data['vawRisk'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    return $data;
}

$assessment = fetchAssessment($pdo, $patientId, $date);
echo json_encode(['status' => 'success', 'data' => $assessment]);
exit;