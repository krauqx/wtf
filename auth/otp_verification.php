<?php
session_start();

$error_message = '';
$success_message = '';

// Handle OTP verification form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_otp'])) {
    $otp = trim($_POST['otp']);
    $phone = $_SESSION['otp_phone'] ?? '';
    
    if (empty($otp)) {
        $error_message = 'Please enter the OTP code.';
    } elseif (strlen($otp) != 6 || !is_numeric($otp)) {
        $error_message = 'Please enter a valid 6-digit OTP code.';
    } else {
        // For demo purposes, accept any 6-digit number as valid OTP
        // In production, you would verify against stored OTP in database
        if (strlen($otp) == 6 && is_numeric($otp)) {
            // OTP verification successful
            $_SESSION['otp_verified'] = true;
            $success_message = 'OTP verified successfully!';
            
            // Redirect to appropriate dashboard based on user role
            if (isset($_SESSION['user_role'])) {
                if ($_SESSION['user_role'] == 'patient') {
                    header('Location: pdash.php');
                } elseif ($_SESSION['user_role'] == 'medical_staff') {
                    header('Location: mwdash.php');
                } elseif ($_SESSION['user_role'] == 'clerk') {
                    header('Location: clerkdash.php');
                } else {
                    header('Location: dashboard.php');
                }
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } else {
            $error_message = 'Invalid OTP code. Please try again.';
        }
    }
}

// Handle resend OTP
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resend_otp'])) {
    $success_message = 'A new OTP has been sent to your phone number.';
}

// Set default phone if none provided (for demo purposes)
if (!isset($_SESSION['otp_phone']) && !isset($_GET['phone'])) {
    $_SESSION['otp_phone'] = '+1 (555) 123-4567';
}

// Set phone from GET parameter if not in session
if (!isset($_SESSION['otp_phone']) && isset($_GET['phone'])) {
    $_SESSION['otp_phone'] = $_GET['phone'];
}
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
            <p class="phone-display"><?php echo htmlspecialchars($_SESSION['otp_phone'] ?? $_GET['phone'] ?? 'your phone number'); ?></p>

            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="otp-form">
                <div class="form-group">
                    <label for="otp">Enter Verification Code</label>
                    <div class="otp-input-container">
                        <input type="text" id="otp" name="otp" maxlength="6" required autocomplete="off" class="otp-input">
                    </div>
                </div>

                <button type="submit" name="verify_otp" class="btn">Verify OTP</button>
            </form>

            <div class="resend-section">
                <p>Didn't receive the code?</p>
                <form method="POST" action="" style="display: inline;">
                    <button type="submit" name="resend_otp" class="resend-btn">Resend OTP</button>
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
                    <div class="feature">
                        <span class="feature-icon">🔒</span>
                        <span>Secure Authentication</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">📱</span>
                        <span>SMS Verification</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">⚡</span>
                        <span>Quick Process</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus on OTP input
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('otp');
            if (otpInput) {
                otpInput.focus();
            }
        });

        // Format OTP input to only accept numbers
        document.getElementById('otp').addEventListener('input', function(e) {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Auto-submit when 6 digits are entered
            if (this.value.length === 6) {
                // Optional: Auto-submit after a short delay
                setTimeout(() => {
                    this.form.submit();
                }, 500);
            }
        });

        // Handle paste events
        document.getElementById('otp').addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const numbers = paste.replace(/[^0-9]/g, '');
            this.value = numbers.substring(0, 6);
        });
    </script>
</body>
</html>
