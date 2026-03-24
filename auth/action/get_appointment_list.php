<?php
session_start();
include_once '../config/config.php'; // Ensure $pdo is available

// Role check: only allow clerks
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'clerk') {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

header('Content-Type: application/json');

try {
    // Fetch appointment requests with patient and doctor info
    $stmt = $pdo->prepare("
        SELECT 
            ar.id,
            ar.appointment_date,
            ar.appointment_time,
            ar.chief_complaint,
            ar.status,
            ar.created_at,
            u_patient.first_name AS patient_first,
            u_patient.last_name AS patient_last,
            u_doctor.first_name AS doctor_first,
            u_doctor.last_name AS doctor_last
        FROM appointment_requests ar
        JOIN users u_patient ON ar.patient_id = u_patient.id
        LEFT JOIN users u_doctor ON ar.doctor_id = u_doctor.id
        ORDER BY ar.created_at DESC
    ");
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format output
    $formatted = array_map(function($row) {
        return [
            'id' => $row['id'],
            'date' => $row['appointment_date'],
            'time' => $row['appointment_time'],
            'patient' => $row['patient_first'] . ' ' . $row['patient_last'],
            'doctor' => $row['doctor_first'] && $row['doctor_last'] 
                        ? 'Dr. ' . $row['doctor_first'] . ' ' . $row['doctor_last'] 
                        : 'Unassigned',
            'complaint' => $row['chief_complaint'],
            'status' => ucfirst($row['status']),
            'submitted' => date('Y-m-d H:i', strtotime($row['created_at']))
        ];
    }, $appointments);

    echo json_encode(['success' => true, 'appointments' => $formatted]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}