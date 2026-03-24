<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simulate clerk role
$_SESSION['user_role'] = 'admin';

// Load config and database connection
require_once __DIR__ . '/../config/config.php'; // Adjust path if needed

// Simulate POST data
$_POST['patient_id'] = 4;
$_POST['service_date'] = '2025-10-24';
$_POST['service_type'] = 'Prenatal Check-up';
$_POST['doctor_id'] = 8; // Replace with a valid user_id from users table where role = 'doctor'
$_POST['service_amount'] = 350.00;
$_POST['notes'] = 'Routine prenatal visit. Mild discomfort reported. Iron supplements advised.';

// Include the service handler
include __DIR__ . '/../auth/action/clerk/clerk_upload_service.php';