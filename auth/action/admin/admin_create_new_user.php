<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../../config/config.php');
header('Content-Type: application/json');

// Allow only admin users


// Collect and validate POST data
$firstName = $_POST['first_name'] ?? '';
$lastName  = $_POST['last_name'] ?? '';
$email     = $_POST['email'] ?? '';
$contact   = $_POST['contact'] ?? '';
$password  = $_POST['password'] ?? '';
$role      = $_POST['role'] ?? '';

if (!$firstName || !$lastName || !$email || !$contact || !$password || !$role) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Validate role
$validRoles = ['patient', 'staff', 'admin', 'midwife', 'clerk'];
if (!in_array($role, $validRoles)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid role']);
    exit;
}

// Validate contact format (E.164: starts with 639 and 9 digits)
if (!preg_match('/^639\d{9}$/', $contact)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid contact number format']);
    exit;
}

// Hash password securely
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("
        INSERT INTO users (
            first_name, last_name, email, contact, password, role
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $firstName,
        $lastName,
        $email,
        $contact,
        $hashedPassword,
        $role
    ]);

    echo json_encode(['status' => 'success', 'message' => 'User created successfully']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Duplicate email
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}