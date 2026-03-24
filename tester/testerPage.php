<?php
session_start();
include_once 'testerGuard.php';

// Simulate role detection
$role = $_SESSION['user_role'] ?? 'none';

echo "<h2>Role Detection Tester</h2>";
echo "<p>Detected Role: <strong>$role</strong></p>";

if ($role === 'patient') {
    echo "<p style='color:green;'>✅ Patient access confirmed.</p>";
} elseif ($role === 'staff') {
    echo "<p style='color:green;'>✅ Staff access confirmed.</p>";
} elseif ($role === 'clerk') {
    echo "<p style='color:green;'>✅ Clerk access confirmed.</p>";
} else {
    echo "<p style='color:red;'>❌ No valid role detected. Access denied.</p>";
}

session_start();
echo "<h2>Session Role Tester</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
