<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');
header('Content-Type: application/json');

// Ensure only clerks or admins can access
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Validate incoming POST data
$patientId     = $_POST['patient_id'] ?? null;
$serviceDate   = $_POST['service_date'] ?? null;
$serviceTypeId = $_POST['service_type'] ?? null; // This is the ID from dropdown
$doctorId      = $_POST['doctor_id'] ?? null;
$serviceAmount = $_POST['service_amount'] ?? null;
$notes         = $_POST['notes'] ?? '';

if (empty($patientId) || empty($serviceDate) || empty($serviceTypeId) || empty($doctorId) || $serviceAmount === null) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Validate doctor role
$check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = ? AND role = 'staff'");
$check->execute([$doctorId]);
if ($check->fetchColumn() == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Selected user is not a staff']);
    exit;
}

// Fetch service name from catalog
$serviceLookup = $pdo->prepare("SELECT service_name FROM service_catalog WHERE service_type_id = ?");
$serviceLookup->execute([$serviceTypeId]);
$serviceName = $serviceLookup->fetchColumn();

if (!$serviceName) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid service type']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Insert into clerk_services
    $stmt = $pdo->prepare("
        INSERT INTO clerk_services (
            patient_id, doctor_id, service_date, service_type, service_amount, notes
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $patientId,
        $doctorId,
        $serviceDate,
        $serviceName, // use actual name
        floatval($serviceAmount),
        $notes
    ]);

    $serviceId = $pdo->lastInsertId();

    // Insert billing transaction as "charge"
    $billStmt = $pdo->prepare("
        INSERT INTO billing_transactions (
            service_id, patient_id, transaction_type, description, amount, applied_by
        ) VALUES (?, ?, 'charge', ?, ?, ?)
    ");
    $billStmt->execute([
        $serviceId,
        $patientId,
        $serviceName, // use actual name
        floatval($serviceAmount),
        $_SESSION['user_id'] ?? null
    ]);

    // Recalculate and update billing balance
    $sumStmt = $pdo->prepare("
        SELECT SUM(amount) AS total FROM billing_transactions WHERE patient_id = ?
    ");
    $sumStmt->execute([$patientId]);
    $total = $sumStmt->fetchColumn() ?? 0.00;

    $balanceStmt = $pdo->prepare("
        INSERT INTO billing_balance (patient_id, total_balance)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE total_balance = VALUES(total_balance), last_updated = NOW()
    ");
    $balanceStmt->execute([$patientId, $total]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Service and billing charge recorded successfully']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}