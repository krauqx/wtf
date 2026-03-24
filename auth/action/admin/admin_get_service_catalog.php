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
    $stmt = $pdo->prepare("SELECT service_type_id AS id, service_name AS name, default_price AS amount FROM service_catalog WHERE is_active = TRUE ORDER BY service_name ASC");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $services
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}