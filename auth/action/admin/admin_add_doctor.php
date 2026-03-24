<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');
header('Content-Type: application/json');

// Only allow admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $_POST['user_id'] ?? null;
$specialization = trim($_POST['specialization'] ?? '');
$schedule = trim($_POST['schedule'] ?? '');

if (!$userId || !$specialization || !$schedule) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    // Ensure user exists and is staff
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || strtolower($user['role']) !== 'staff') {
        echo json_encode(['status' => 'error', 'message' => 'User is not staff']);
        exit;
    }

    // Prevent duplicate doctor profile
    $check = $pdo->prepare("SELECT doctor_id FROM doctor WHERE user_id = ?");
    $check->execute([$userId]);
    if ($check->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Doctor profile already exists']);
        exit;
    }

    // Insert doctor profile
    $insert = $pdo->prepare("INSERT INTO doctor (user_id, specialization, schedule) VALUES (?, ?, ?)");
    $insert->execute([$userId, $specialization, $schedule]);

    echo json_encode(['status' => 'success', 'message' => 'Doctor profile created']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}