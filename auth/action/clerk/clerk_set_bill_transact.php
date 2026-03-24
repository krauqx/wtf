<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once '../../../config/config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$serviceId = $input['service_id'] ?? null;
$patientId = $input['patient_id'] ?? null;
$type = $input['transaction_type'] ?? null;
$description = $input['description'] ?? '';
$amount = $input['amount'] ?? 0.00;

if (!$serviceId || !$patientId || !$type || !is_numeric($amount)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid input']);
    exit;
}

// Convert discount and payment to negative amounts
if ($type === 'discount' || $type === 'payment') {
    $amount = -abs($amount);
}

try {
    $pdo->beginTransaction();

    // Insert transaction
    $stmt = $pdo->prepare("
        INSERT INTO billing_transactions (
            service_id, patient_id, transaction_type, description, amount, applied_by
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $serviceId,
        $patientId,
        $type,
        $description,
        $amount,
        $_SESSION['user_id'] ?? null
    ]);

    // Recalculate balance
    $sumStmt = $pdo->prepare("
        SELECT SUM(amount) AS total FROM billing_transactions WHERE patient_id = ?
    ");
    $sumStmt->execute([$patientId]);
    $total = $sumStmt->fetchColumn() ?? 0.00;

    // Update or insert billing_balance
    $balanceStmt = $pdo->prepare("
        INSERT INTO billing_balance (patient_id, total_balance)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE total_balance = VALUES(total_balance), last_updated = NOW()
    ");
    $balanceStmt->execute([$patientId, $total]);

    // Get last payment info
    $lastPaymentStmt = $pdo->prepare("
        SELECT amount, created_at
        FROM billing_transactions
        WHERE patient_id = ? AND transaction_type = 'payment'
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $lastPaymentStmt->execute([$patientId]);
    $lastPayment = $lastPaymentStmt->fetch(PDO::FETCH_ASSOC);

    $pdo->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction recorded and balance updated',
        'last_payment' => $lastPayment ?: ['amount' => 0.00, 'created_at' => null]
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}