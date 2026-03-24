<?php
session_start();
header('Content-Type: application/json');

require_once '../../../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$patientId = $data['patient_id'] ?? null;
$lmpDate = $data['lmp_date'] ?? null;
$edcDate = $data['edc_date'] ?? null;
$notes = $data['notes'] ?? null;

if (!$patientId || !$lmpDate || !$edcDate) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert or update (requires UNIQUE constraint on patient_id)
    $stmt = $pdo->prepare("
        INSERT INTO pregnancy_tracker (patient_id, lmp_date, edc_date, notes)
        VALUES (:patient_id, :lmp_date, :edc_date, :notes)
        ON DUPLICATE KEY UPDATE
            lmp_date = VALUES(lmp_date),
            edc_date = VALUES(edc_date),
            notes = VALUES(notes),
            updated_at = CURRENT_TIMESTAMP
    ");

    $stmt->execute([
        ':patient_id' => $patientId,
        ':lmp_date' => $lmpDate,
        ':edc_date' => $edcDate,
        ':notes' => $notes
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Pregnancy tracker saved or updated']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}