<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

// Ensure POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit;
}

// Extract and sanitize inputs
$patient_id        = $_SESSION['user_id'];
$doctor_user_id    = $_POST['doctor'] ?? '';
$appointment_date  = $_POST['date'] ?? '';
$appointment_time  = $_POST['time'] ?? '';
$chief_complaint   = trim($_POST['complaint'] ?? '');

// Validate required fields
$missing = [];
if (!$doctor_user_id)     $missing[] = 'Doctor';
if (!$appointment_date)   $missing[] = 'Date';
if (!$appointment_time)   $missing[] = 'Time';
if (!$chief_complaint)    $missing[] = 'Chief Complaint';

if (!empty($missing)) {
    $response['message'] = 'Missing fields: ' . implode(', ', $missing);
    echo json_encode($response);
    exit;
}

// Confirm doctor exists in doctor table
try {
    $stmt = $pdo->prepare("SELECT doctor_id FROM doctor WHERE user_id = ?");
    $stmt->execute([$doctor_user_id]);
    $doctor = $stmt->fetch();

    if (!$doctor) {
        $response['message'] = 'Doctor ID "' . htmlspecialchars($doctor_user_id) . '" not found in doctor records.';
        echo json_encode($response);
        exit;
    }

    $doctor_id = $doctor['doctor_id'];
} catch (PDOException $e) {
    $response['message'] = 'Doctor lookup failed: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

// Insert appointment request
try {
    $stmt = $pdo->prepare("
        INSERT INTO appointment_requests (
            patient_id, doctor_id, appointment_date, appointment_time, chief_complaint, status
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $patient_id,
        $doctor_user_id, // directly use users.id
        $appointment_date,
        $appointment_time,
        $chief_complaint,
        'pending'
    ]);

    $response['success'] = true;
    $response['message'] = 'Appointment request submitted successfully.';
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}


echo json_encode($response);