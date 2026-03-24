<?php
session_start();
include_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../api/sms/sendSMS.php';

function requestOtpForContact($contact, $channel = 'sms', $purpose = 'register') {
    global $pdo;

    // 🔍 STEP 1: Prevent OTP spam 
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM otp_requests
            WHERE contact = ? AND status = 'pending' AND created_at > NOW() - INTERVAL 1 MINUTE
        ");
        $stmt->execute([$contact]);
        if ($stmt->fetchColumn() > 0) {
            return [
                "status" => "error",
                "message" => "OTP already sent recently. Please wait before retrying."
            ];
        }
    } catch (PDOException $e) {
        return ["status" => "error", "message" => "DB error: " . $e->getMessage()];
    }

    // 🔢 STEP 2: Generate OTP
    $otp = rand(100000, 999999);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // 💾 STEP 3: Save OTP
    try {
        $stmt = $pdo->prepare("
            INSERT INTO otp_requests (contact, otp_code, channel, purpose, expires_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$contact, $otp, $channel, $purpose, $expiresAt]);
    } catch (PDOException $e) {
        return ["status" => "error", "message" => "DB error: " . $e->getMessage()];
    }

    // 📩 STEP 4: Send SMS
    $message = "Your JAM OTP is: $otp. It expires in 5 minutes.";
    $smsResult = sendSMS($contact, $message);

    return [
        "status" => "success",
        "otp" => $otp,
        "expires_at" => $expiresAt,
        "sms_status" => $smsResult
    ];
}

// 🧪 Handle POST from signup.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['first_name', 'last_name', 'email', 'contact', 'password', 'confirm_password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("Missing field: $field");
        }
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        die("Passwords do not match.");
    }

    // 🧠 Store user data in session
    $otpId = $pdo->lastInsertId();
    
    $_SESSION['pending_user'] = [
        'first_name' => trim($_POST['first_name']),
        'last_name'  => trim($_POST['last_name']),
        'email'      => trim($_POST['email']),
        'contact'    => trim($_POST['contact']),
        'role'       => $_POST['role'] ?? 'patient',
        'password'   => password_hash($_POST['password'], PASSWORD_DEFAULT)
    ];
    
    // 🔁 Request OTP
    $result = requestOtpForContact($_POST['contact'], 'sms', 'register');
    if ($result['status'] === 'success') {
    $_SESSION['pending_user']['otp'] = $result['otp'];       // ✅ Store OTP if needed
    $_SESSION['pending_user']['otp_id'] = $pdo->lastInsertId(); // ✅ Store OTP ID
    header("Location: otp_page.php");
    exit;
    }

    if ($result['status'] === 'success') {
        header("Location: otp_page.php");
        exit;
    } else {
        echo "OTP Error: " . $result['message'];
    }
}
?>
