<?php
// test_check_user_role.php

session_start();

// Simulate login for testing (optional)
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'admin'; // Change to 'staff', 'patient', etc. for testing
}

// Check and display role
if (isset($_SESSION['user_role'])) {
    echo "✅ Current user role: <strong>" . htmlspecialchars($_SESSION['user_role']) . "</strong>";
} else {
    echo "⚠️ No user role is set in session.";
}
?>