<?php
// Tester for submit_appointment_request.php
//Lol pota di pa nagana tester. Yung main ang gumagana HAHAHAHAHA
$endpoint = 'http://localhost/JAM_LYINGIN/auth/action/submit_appointment_request.php'; // Adjust path if needed

// Simulated form data
$postData = [
    'doctor'    => 'Dr. staff1 staff1',
    'date'      => '2025-10-21',
    'time'      => '09:30',
    'complaint' => 'Prenatal checkup'
];

// Simulate session (if needed)
session_start();
$_SESSION['user_id'] = 3; // Replace with a valid patient ID from your DB

// Prepare cURL
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

// Execute and decode response
$response = curl_exec($ch);
curl_close($ch);

// Output result
echo "<pre>";
echo "Response from submit_appointment_request.php:\n";
print_r(json_decode($response, true));
echo "</pre>";

echo "<pre>";
echo "Raw response:\n";
echo htmlspecialchars($response);
echo "\n\nDecoded:\n";
print_r(json_decode($response, true));
echo "</pre>";
