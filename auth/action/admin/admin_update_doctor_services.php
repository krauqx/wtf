<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');
header('Content-Type: application/json');

// Only allow admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $_POST['user_id'] ?? null;
$services = $_POST['services'] ?? []; // array of service_type_id

if (!$userId || !is_numeric($userId)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid doctor user ID']);
    exit;
}

try {
    // Get doctor_id from user_id
    $stmt = $pdo->prepare("SELECT doctor_id FROM doctor WHERE user_id = ?");
    $stmt->execute([$userId]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        echo json_encode(['status' => 'error', 'message' => 'Doctor profile not found']);
        exit;
    }

    $doctorId = $doctor['doctor_id'];

    $pdo->beginTransaction();

    // Clear existing services
    $pdo->prepare("DELETE FROM doctor_services WHERE doctor_id = ?")->execute([$doctorId]);

    // Insert new services
    if (!empty($services)) {
        $insert = $pdo->prepare("INSERT INTO doctor_services (doctor_id, service_type_id) VALUES (?, ?)");
        foreach ($services as $sid) {
            if (is_numeric($sid)) {
                $insert->execute([$doctorId, $sid]);
            }
        }
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Doctor services updated']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}