<?php
require_once '../../../config/config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$currentPassword = $input['current_password'] ?? '';
$newPassword = $input['new_password'] ?? '';

// Only allow clerk role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'clerk') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}


$userId = $_SESSION['user_id'];

try {
    // Fetch current password for clerk role
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? AND role = 'clerk'");
    $stmt->execute([$userId]);
    $stored = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stored || !password_verify($currentPassword, $stored['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
        exit;
    }

    // Update password
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'clerk'");
    $update->execute([$newHash, $userId]);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}