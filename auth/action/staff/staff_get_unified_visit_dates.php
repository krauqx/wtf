<?php
session_start();
require_once '../../../config/config.php';




header('Content-Type: application/json');

$patientId = $_SESSION['selectedPatientId'] ?? 0;

if (!$patientId) {
    echo json_encode(['status' => 'error', 'message' => 'No patient selected']);
    exit;
}

function getUnifiedVisitDates(PDO $pdo, int $patientId): array {
    $queries = [
        "SELECT visit_date FROM vaw_risk_assessment WHERE patient_id = :id",
        "SELECT exam_date FROM physical_examination_record WHERE patient_id = :id",
        "SELECT visit_date FROM obstetrical_history WHERE patient_id = :id",
        "SELECT visit_date FROM medical_history WHERE patient_id = :id"
    ];

    $dates = [];

    foreach ($queries as $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $patientId]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $date = reset($row); // gets the first column value
            if (!empty($date)) {
                $dates[] = $date;
            }
        }
    }

    $uniqueDates = array_unique($dates);
    rsort($uniqueDates); // latest first

    return $uniqueDates;
}

$visitDates = getUnifiedVisitDates($pdo, $patientId);

echo json_encode(['status' => 'success', 'dates' => $visitDates]);
exit;