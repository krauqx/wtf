<?php
// Simulate staff role and user ID
$testStaffId = 8;
$_SESSION['user_role'] = 'staff';

// Build the request URL
$url = "http://localhost/JAM_LYINGIN/auth/action/staff/staff_get_approved_appointments.php";

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request
$response = curl_exec($ch);
curl_close($ch);

// Debug raw response
if (!$response) {
    echo "<p>❌ No response received from endpoint.</p>";
    exit;
}

// Decode JSON
$data = json_decode($response, true);

// Validate structure
if (!is_array($data) || !isset($data['status'])) {
    echo "<p>❌ Invalid response format. Raw output:</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    exit;
}

echo "<h2>Approved Appointments for Staff ID: {$testStaffId}</h2>";

if ($data['status'] === 'success') {
    echo "<ul>";
    foreach ($data['data'] as $appt) {
        echo "<li>";
        echo "<strong>Date:</strong> " . htmlspecialchars($appt['appointment_date']) . "<br>";
        echo "<strong>Time:</strong> " . htmlspecialchars($appt['appointment_time']) . "<br>";
        echo "<strong>Patient ID:</strong> " . htmlspecialchars($appt['patient_id']) . "<br>";
        echo "<strong>Concern:</strong> " . htmlspecialchars($appt['chief_complaint']) . "<br>";
        echo "<strong>Status:</strong> " . htmlspecialchars($appt['status']) . "<br>";
        echo "</li><hr>";
    }
    echo "</ul>";
} else {
    echo "<p>Error: " . htmlspecialchars($data['message']) . "</p>";
}