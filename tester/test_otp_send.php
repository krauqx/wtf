<?php
require_once '../config/config.php'; // Contains $pdo
require_once '../auth/request_otp.php';      // Contains requestOtpForContact()

// 🔧 Test values
$testContact = '+639949880737';        // Replace with your test number or email
$testChannel = 'sms';                // 'sms' or 'email'
$testPurpose = 'register';           // 'register', 'verify', etc.

// 🧪 Call the function directly
$result = requestOtpForContact($testContact, $testChannel, $testPurpose);

// 🖨️ Output result for debugging
echo "<pre>";
print_r($result);
echo "</pre>";
?>