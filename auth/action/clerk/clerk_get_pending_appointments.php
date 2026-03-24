<?php
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../../config/config.php';

try {
    $sql = "
        SELECT 
            ar.id AS appointment_id,
            ar.patient_id,
            ar.doctor_id,
            ar.appointment_date,
            ar.appointment_time,
            ar.chief_complaint,
            ar.status,
            CONCAT(COALESCE(pr.first_name, 'Unknown'), ' ', COALESCE(pr.last_name, '')) AS patient_name,
            CONCAT('Dr. ', COALESCE(ud.first_name,''), ' ', COALESCE(ud.last_name,'')) AS doctor_name,
            d.schedule AS doctor_schedule
        FROM appointment_requests ar
        LEFT JOIN patient_records pr ON ar.patient_id = pr.user_id
        LEFT JOIN users ud ON ar.doctor_id = ud.id
        LEFT JOIN doctor d ON d.user_id = ud.id
        WHERE ar.status IS NOT NULL AND LOWER(ar.status) = 'pending'
        ORDER BY ar.appointment_date ASC, ar.appointment_time ASC
    ";



    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $appointments,
        'message' => count($appointments) ? null : 'No pending appointments found'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
