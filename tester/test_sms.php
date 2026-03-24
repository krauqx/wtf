<?php
// 🔗 STEP 1: Load config variables
require_once '../config/config.php';

// 🔧 STEP 2: Setup message details
$recipient = "639949880737";                              // Recipient's mobile number
$message = "Hello from JAM LyingIn!(Mocean)";             // Message content
$sender_name = "JAM LyingIn";                             // Sender name (max 11 chars)

// 📦 STEP 3: Prepare the data payload
$data = array(
    'mocean-to' => $recipient,
    'mocean-from' => $sender_name,
    'mocean-text' => $message
);

// 🚀 STEP 4: Initialize cURL and send the request
$ch = curl_init($sms_send_endpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer $sms_token",
    "Content-Type: application/x-www-form-urlencoded"
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// 📬 STEP 5: Output the response
echo "MoceanAPI Response: " . $response;
?>
