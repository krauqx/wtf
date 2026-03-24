<?php

// Set the target patient ID for testing
$testPatientId = 4;
$_SESSION['user_role'] = 'admin'; // Simulate clerk role
// Build the request URL
$url = "http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_get_services_by_patient.php?patient_id=" . urlencode($testPatientId);

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request
$response = curl_exec($ch);
curl_close($ch);

// Decode and display the result
$data = json_decode($response, true);

echo "<h2>Service List for Patient ID: {$testPatientId}</h2>";

if ($data['status'] === 'success') {
    echo "<ul>";
    foreach ($data['data'] as $service) {
        echo "<li>";
        echo "<strong>Date:</strong> {$service['service_date']}<br>";
        echo "<strong>Type:</strong> {$service['service_type']}<br>";
        echo "<strong>Amount:</strong> ₱{$service['service_amount']}<br>";
        echo "<strong>Doctor:</strong> {$service['doctor_name']}<br>";
        echo "<strong>Notes:</strong> {$service['notes']}<br>";
        echo "</li><hr>";
    }
    echo "</ul>";
} else {
    echo "<p>Error: {$data['message']}</p>";
}