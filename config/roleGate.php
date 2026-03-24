<?php
// Session and role-based access control for JAM Lying-in

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define redirect URLs for each role
$_SESSION['redirect_urls'] = [
    'staff'   => 'http://localhost/JAM_Lyingin/dashboard.php',
    'admin'   => 'http://localhost/JAM_Lyingin/dashboard.php', // Admins now share staff dashboard
    'patient' => 'http://localhost/JAM_Lyingin/pdash.php',
    'clerk'   => 'http://localhost/JAM_Lyingin/clerkdash.php',
    'default' => 'http://localhost/JAM_Lyingin/front.php'
];

// Log access attempts
function logAccess($message) {
    $timestamp = date('Y-m-d H:i:s');
    $ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user      = $_SESSION['user_email'] ?? 'guest';
    $role      = $_SESSION['user_role'] ?? 'none';
    $page      = $_SERVER['REQUEST_URI'] ?? 'unknown';

    $entry = "[$timestamp] IP: $ip | User: $user | Role: $role | Page: $page | $message\n";

    // Optional: write to log file
    // file_put_contents(__DIR__ . '/access.log', $entry, FILE_APPEND);
}

// Role-based access guard
function requireRole(array $allowedRoles) {
    $actualRole = $_SESSION['user_role'] ?? null;
    $urls       = $_SESSION['redirect_urls'] ?? [];

    if (!$actualRole) {
        logAccess("No role detected. Redirecting to front.php");
        header("Location: " . ($urls['default'] ?? '/'));
        exit;
    }

    if (!in_array($actualRole, $allowedRoles)) {
        $redirectUrl = $urls[$actualRole] ?? $urls['default'] ?? '/';
        logAccess("Role mismatch. Redirecting to $redirectUrl");
        header("Location: $redirectUrl");
        exit;
    }

    logAccess("Access granted to role: $actualRole");
}