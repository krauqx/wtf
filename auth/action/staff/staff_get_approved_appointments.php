<?php
session_start();
header('Content-Type: application/json');

require_once '../../../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$staffId = $_SESSION['user_id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Join with patient_records to get patient name
    $stmt = $pdo->prepare("
        SELECT 
            ar.id,
            ar.appointment_date,
            ar.appointment_time,
            ar.chief_complaint,
            ar.status,
            ar.patient_id,
            ar.doctor_id,
            CONCAT(pr.first_name, ' ', pr.last_name) AS patient_name
        FROM appointment_requests ar
        JOIN patient_records pr ON ar.patient_id = pr.patient_id
        WHERE LOWER(ar.status) IN ('approved','waiting','ongoing','done','cancelled')
          AND ar.doctor_id = ?
        ORDER BY ar.appointment_date ASC, ar.appointment_time ASC
    ");
    $stmt->execute([$staffId]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($appointments)) {
        echo json_encode([
            'status' => 'empty',
            'message' => 'No matching appointments found.'
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'data' => $appointments
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}