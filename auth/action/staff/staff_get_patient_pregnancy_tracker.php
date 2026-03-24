<?php
session_start();
header('Content-Type: application/json');

require_once '../../../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing patient ID']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT tracker_id, patient_id, lmp_date, edc_date, aog_weeks, notes
        FROM pregnancy_tracker
        WHERE patient_id = ?
        LIMIT 1
    ");
    $stmt->execute([$patientId]);
    $tracker = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tracker) {
        echo json_encode(['status' => 'success', 'data' => $tracker]);
    } else {
        echo json_encode(['status' => 'empty', 'message' => 'No tracker found for this patient']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}