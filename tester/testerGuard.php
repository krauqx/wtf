<?php
// config/testerGuard.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define redirect URLs
$_SESSION['redirect_urls'] = [
    'staff'   => '/JAM_Lyingin/dashboard.php',
    'patient' => '/JAM_Lyingin/pdash.php',
    'clerk'   => '/JAM_Lyingin/clerkdash.php',
    'default' => '/JAM_Lyingin/front.php'
];

/**
 * Simple role tester — no logging, no redirection.
 *
 * @param string $expectedRole The role required to access the page.
 */
function testRole($expectedRole) {
    $actualRole = $_SESSION['user_role'] ?? 'none';

    echo "<h3>Role Check</h3>";
    echo "<p>Expected Role: <strong>$expectedRole</strong></p>";
    echo "<p>Actual Role: <strong>$actualRole</strong></p>";

    if ($actualRole === $expectedRole) {
        echo "<p style='color:green;'>✅ Access granted.</p>";
    } else {
        echo "<p style='color:red;'>❌ Access denied.</p>";
    }
}