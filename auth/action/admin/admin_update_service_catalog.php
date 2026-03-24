<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');
header('Content-Type: application/json');

// Only allow admin users
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     http_response_code(403);
//     echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
//     exit;
// }

try {
    $pdo->beginTransaction();

    foreach ($_POST as $key => $value) {
        if (preg_match('/^service_amount_(\d+)$/', $key, $matches)) {
            $serviceId = $matches[1];
            $amount = floatval($value);

            $stmt = $pdo->prepare("UPDATE service_catalog SET default_price = ? WHERE service_type_id = ?");
            $stmt->execute([$amount, $serviceId]);
        }
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Service amounts updated']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}