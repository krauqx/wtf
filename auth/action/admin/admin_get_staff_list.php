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
    // ✅ Fetch staff users who are not yet doctors
    $stmt = $pdo->query("
        SELECT u.id, u.first_name, u.last_name, u.email, u.contact
        FROM users u
        WHERE u.role = 'staff'
          AND NOT EXISTS (
              SELECT 1 FROM doctor d WHERE d.user_id = u.id
          )
        ORDER BY u.first_name, u.last_name
    ");

    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $staff]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}