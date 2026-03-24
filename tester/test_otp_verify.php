<?php
require_once '../config/config.php';
require_once '../auth/verify_otp.php'; // Contains verifyOtpForContact()
?>

<!DOCTYPE html>
<html>
<head>
  <title>Test OTP Verification</title>
</head>
<body>
  <h2>Test OTP Verification</h2>
  <form method="POST">
    <label for="contact">Contact (e.g., 639xxxxxxxxx):</label><br>
    <input type="text" name="contact" id="contact" required><br><br>

    <label for="otp">Enter OTP:</label><br>
    <input type="number" name="otp" id="otp" required><br><br>

    <button type="submit">Verify OTP</button>
  </form>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $contact = $_POST['contact'] ?? '';
      $otp = $_POST['otp'] ?? '';

      if (empty($contact) || empty($otp)) {
          echo "<p style='color:red;'>Missing contact or OTP.</p>";
      } else {
      
      }
  }
  ?>
</body>
</html>