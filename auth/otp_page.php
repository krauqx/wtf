<?php
session_start();

// ✅ Check if user data exists
if (!isset($_SESSION['pending_user'])) {
    die("Session expired. Please restart registration.");
}

$contact = $_SESSION['pending_user']['contact'] ?? '';
$otp     = $_SESSION['pending_user']['otp'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - JAM Lying-In Clinic</title>
    <link rel="stylesheet" href="otp_verification.css">
</head>
<body>
    <nav class="navbar">
        <a href="front.html" class="navbar-brand">JAM Lying-In Clinic</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="mslogin.php" class="nav-link">Medical Staff</a></li>
            <li class="nav-item"><a href="login.php?role=patient" class="nav-link">Patient Sign In</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="form-container">
            <div class="logo-container">
                <img src="logo.png" alt="JAM Lying-In Clinic Logo" class="logo">
            </div>

            <h1>Verify Your Phone Number</h1>
            <p class="subtitle">We've sent a 6-digit verification code to</p>
            <p class="phone-display"><?= htmlspecialchars($contact) ?></p>

            <?php if (!empty($otp)): ?>
                <div class="alert alert-dev">[Dev Mode] OTP: <strong><?= htmlspecialchars($otp) ?></strong></div>
            <?php endif; ?>

            <form method="POST" action="verify_otp.php" class="otp-form">
                <div class="form-group">
                    <label for="otp_code">Enter Verification Code</label>
                    <div class="otp-input-container">
                        <input type="text" id="otp_code" name="otp_code" pattern="\d{6}" maxlength="6" required autocomplete="off" class="otp-input">
                    </div>
                </div>
                <input type="hidden" name="contact" value="<?= htmlspecialchars($contact) ?>">
                <button type="submit" class="btn">Verify OTP</button>
            </form>

            <div class="resend-section">
                <p>Didn't receive the code?</p>
                <form method="POST" action="request_otp.php" style="display: inline;">
                    <input type="hidden" name="contact" value="<?= htmlspecialchars($contact) ?>">
                    <button type="submit" class="resend-btn">Resend OTP</button>
                </form>
            </div>

            <div class="back-section">
                <a href="login.php" class="back-link">← Back to Login</a>
            </div>
        </div>

        <div class="welcome-container">
            <div class="welcome-content">
                <h2>Secure Verification</h2>
                <p>We're ensuring your account security by verifying your phone number. This helps protect your personal information and medical records.</p>
                <div class="security-features">
                    <div class="feature"><span class="feature-icon">🔒</span><span>Secure Authentication</span></div>
                    <div class="feature"><span class="feature-icon">📱</span><span>SMS Verification</span></div>
                    <div class="feature"><span class="feature-icon">⚡</span><span>Quick Process</span></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('otp_code');
            if (otpInput) otpInput.focus();

            otpInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 6) {
                    setTimeout(() => this.form.submit(), 500);
                }
            });

            otpInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                this.value = paste.replace(/[^0-9]/g, '').substring(0, 6);
            });
        });
    </script>
</body>
</html>