<?php
session_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Validate session and role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Validate input
$year = isset($_GET['year']) ? intval($_GET['year']) : null;
$month = isset($_GET['month']) ? intval($_GET['month']) : null;

if ($year < 1000 || $month < 1 || $month > 12) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid year or month'
    ]);
    exit;
}

require_once __DIR__ . '/../../../config/config.php';

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection not initialized'
    ]);
    exit;
}

try {
    // Query approved appointment dates for the given month
    $stmt = $pdo->prepare("
        SELECT DISTINCT DATE(appointment_date) AS appointment_day
        FROM appointment_requests
        WHERE status = 'approved'
          AND YEAR(appointment_date) = ?
          AND MONTH(appointment_date) = ?
        ORDER BY appointment_day ASC
    ");
    $stmt->execute([$year, $month]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'status' => 'success',
        'dates' => $dates
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error'
    ]);
}