<?php
session_start();
require_once __DIR__ . '/../../config/config.php'; // defines $pdo

// Validate patient ID from session
if (!isset($_SESSION['patient_id']) || !is_numeric($_SESSION['patient_id'])) {
    die("Missing or invalid patient ID.");
}

$patientId = (int) $_SESSION['patient_id'];

// Validate and sanitize POST inputs
$visitDate        = $_POST['visit_date']        ?? null;
$bloodPressure    = $_POST['bp']                ?? null;
$temperature      = $_POST['temp']              ?? null;
$weight           = $_POST['weight']            ?? null;
$fundalHeight     = $_POST['fundal_height']     ?? null;
$fetalHeartTone   = $_POST['fetal_heart_tone']  ?? null;
$fetalPosition    = $_POST['fetal_position']    ?? null;
$chiefComplaint   = $_POST['chief_complaint']   ?? null;

// Basic validation
if (
    !$visitDate || !$bloodPressure || !$temperature || !$weight ||
    !$fundalHeight || !$fetalHeartTone || !$fetalPosition || !$chiefComplaint
) {
    die("All fields are required.");
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO visit_analytics (
            patient_id, visit_date, blood_pressure, temperature, weight,
            fundal_height, fetal_heart_tone, fetal_position, chief_complaint
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $patientId,
        $visitDate,
        $bloodPressure,
        (float) $temperature,
        (float) $weight,
        (float) $fundalHeight,
        (int) $fetalHeartTone,
        $fetalPosition,
        $chiefComplaint
    ]);

    // Redirect or respond
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;


} catch (PDOException $e) {
    echo "<p>Error saving visit analytics: " . htmlspecialchars($e->getMessage()) . "</p>";
}