<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');
header('Content-Type: application/json');

// Simulate role for direct execution or testing
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'admin'; // fallback role for testing
}

// Clerk/admin access only
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            service_type_id,
            service_name,
            default_price,
            description,
            is_active
        FROM service_catalog
        WHERE is_active = 1
        ORDER BY service_name ASC
    ");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $services]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}