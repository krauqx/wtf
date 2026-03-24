<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../../../config/config.php');
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT user_id FROM doctor");
    $doctorIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $update = $pdo->prepare("UPDATE doctor SET schedule = ? WHERE user_id = ?");

    foreach ($doctorIds as $userId) {
        $key = 'schedule_' . $userId;
        if (isset($_POST[$key])) {
            $schedule = trim($_POST[$key]);
            $update->execute([$schedule, $userId]);
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Schedules updated']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}