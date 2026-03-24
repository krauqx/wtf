<?php
session_start();

// Suppress HTML errors, log instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Check session and role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Session invalid or unauthorized role'
    ]);
    exit;
}

require_once __DIR__ . '/../../../config/config.php';

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection not initialized'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$date = $_GET['date'] ?? null;

// Optional: validate date format
if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    error_log("Invalid date format: $date");
    $date = null;
}

try {
    // Step 1: Resolve patient_id
    $stmt = $pdo->prepare("SELECT patient_id FROM patient_records WHERE user_id = ?");
    $stmt->execute([$userId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No patient record found for this user'
        ]);
        exit;
    }

    $patientId = $patient['patient_id'];
    error_log("Resolved patient_id: $patientId for user_id: $userId");

    // Step 2: Fetch medical history records with doctor name
    $sql = "
        SELECT 
            mh.*, 
            CONCAT('Dr. ', COALESCE(u.first_name, 'Unknown'), ' ', COALESCE(u.last_name, '')) AS examined_by
        FROM medical_history mh
        LEFT JOIN users u ON mh.staff_id = u.id
        WHERE mh.patient_id = ?
    ";
    $params = [$patientId];

    if ($date) {
        $sql .= " AND DATE(mh.visit_date) = ?";
        $params[] = $date;
        error_log("Filtering medical history by date: $date");
    }

    $sql .= " ORDER BY mh.visit_date DESC";

    error_log("[SQL] Executing: $sql");
    error_log("[Params] " . json_encode($params));

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $records,
        'message' => count($records) ? null : 'No medical history records found'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO error: " . $e->getMessage());
    error_log("PDO trace: " . print_r($stmt->errorInfo(), true));
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}