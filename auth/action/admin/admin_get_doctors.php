<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');
header('Content-Type: application/json');

// ✅ Only allow admin users
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    // ✅ Join doctor with users to get doctor profiles
    $stmt = $pdo->query("
        SELECT d.doctor_id, u.id AS user_id, u.first_name, u.last_name, u.email,
               d.specialization, d.schedule
        FROM doctor d
        INNER JOIN users u ON d.user_id = u.id
        ORDER BY u.first_name, u.last_name
    ");

    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $doctors
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}