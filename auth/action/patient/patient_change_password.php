<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once '../../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
    ]);
    exit;
}

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($current_password) || empty($new_password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Current password and new password are required'
    ]);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode([
        'status' => 'error',
        'message' => 'New password must be at least 6 characters long'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found'
        ]);
        exit;
    }

    if (!password_verify($current_password, $user['password'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Current password is incorrect'
        ]);
        exit;
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->execute([$hashed_password, $_SESSION['user_id']]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Password changed successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}