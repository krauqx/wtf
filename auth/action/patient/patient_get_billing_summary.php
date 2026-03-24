<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');
header('Content-Type: application/json');

// Validate access
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['clerk', 'admin', 'patient'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$patientId = $_GET['patient_id'] ?? null;
if (!$patientId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing patient ID']);
    exit;
}

try {
    // Get total balance from billing_balance
    $balanceStmt = $pdo->prepare("
        SELECT total_balance
        FROM billing_balance
        WHERE patient_id = ?
    ");
    $balanceStmt->execute([$patientId]);
    $totalBalance = $balanceStmt->fetchColumn() ?? 0.00;

    // Get last payment
    $paymentStmt = $pdo->prepare("
        SELECT amount, created_at
        FROM billing_transactions
        WHERE patient_id = ? AND transaction_type = 'payment'
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $paymentStmt->execute([$patientId]);
    $lastPayment = $paymentStmt->fetch(PDO::FETCH_ASSOC) ?: ['amount' => 0.00, 'created_at' => null];

    // Get all transactions
    $txStmt = $pdo->prepare("
        SELECT created_at AS date, description, amount, transaction_type
        FROM billing_transactions
        WHERE patient_id = ?
        ORDER BY created_at DESC
    ");
    $txStmt->execute([$patientId]);
    $transactions = $txStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'balance' => $totalBalance,
        'last_payment' => $lastPayment,
        'transactions' => $transactions
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}