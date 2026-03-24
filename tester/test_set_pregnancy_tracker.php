<?php
// Simulate staff role
$_SESSION['user_role'] = 'staff';

// Test data
$testPayload = [
    'patient_id' => 4, // Replace with a valid patient_id
    'lmp_date' => '2025-09-15',
    'edc_date' => '2026-06-22', // Typically LMP + 280 days
    'notes' => 'Regular cycle, no complications'
];

// Build request
$url = "http://localhost/JAM_LYINGIN/auth/action/staff/staff_set_patient_pregnancy_tracker.php";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testPayload));

// Execute
$response = curl_exec($ch);
curl_close($ch);

// Debug
if (!$response) {
    echo "<p>❌ No response received from endpoint.</p>";
    exit;
}

$data = json_decode($response, true);

if (!is_array($data) || !isset($data['status'])) {
    echo "<p>❌ Invalid response format. Raw output:</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    exit;
}

echo "<h2>Pregnancy Tracker Submission</h2>";

if ($data['status'] === 'success') {
    echo "<p>✅ Success: " . htmlspecialchars($data['message']) . "</p>";
} else {
    echo "<p>❌ Error: " . htmlspecialchars($data['message']) . "</p>";
}