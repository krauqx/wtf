<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once '../../../config/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'patient') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            d.doctor_id,
            u.id AS user_id,
            CONCAT(u.first_name, ' ', u.last_name) AS full_name,
            u.email,
            u.contact,
            d.specialization,
            d.schedule
        FROM doctor d
        INNER JOIN users u ON d.user_id = u.id
        ORDER BY u.first_name ASC, u.last_name ASC
    ");
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($doctors) {
        echo json_encode(['status' => 'success', 'data' => $doctors]);
    } else {
        echo json_encode(['status' => 'empty', 'message' => 'No doctors found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}