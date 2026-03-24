<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$selectedPatientId = intval($input['selectedPatientId'] ?? 0);

if ($selectedPatientId > 0) {
    $_SESSION['selectedPatientId'] = $selectedPatientId;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid patient ID']);
}
