<?php
session_start(); // ✅ Required to access session
require_once '../config/config.php'; // Contains $pdo

function verifyOtpForContact($contact, $otp) {
    global $pdo;

    $otp = trim((string)$otp);
    $contact = trim($contact);

    $stmt = $pdo->prepare("
        SELECT id, otp_code, expires_at, status
        FROM otp_requests
        WHERE contact = ? AND status = 'pending'
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$contact]);
    $record = $stmt->fetch();

    if (!$record) {
        return ["status" => "error", "message" => "No pending OTP found for this contact."];
    }

    if ($record['otp_code'] !== $otp) {
        return ["status" => "error", "message" => "Incorrect OTP."];
    }

    if (strtotime($record['expires_at']) < time()) {
        $pdo->prepare("UPDATE otp_requests SET status = 'expired' WHERE id = ?")->execute([$record['id']]);
        return ["status" => "error", "message" => "OTP has expired."];
    }

    $pdo->prepare("
        UPDATE otp_requests
        SET status = 'used', used_at = NOW()
        WHERE id = ?
    ")->execute([$record['id']]);

    return ["status" => "success", "message" => "OTP verified successfully."];
}

// 🧪 POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact = $_POST['contact'] ?? '';
    $otp = $_POST['otp_code'] ?? '';

    if (empty($contact)) {
        die("Missing contact.");
    }

    if (empty($otp)) {
        die("Missing OTP.");
    }

    $result = verifyOtpForContact($contact, $otp);

    if ($result['status'] === 'success') {
        // ✅ Redirect to finalize registration
        header("Location: action/finalize_signup.php");
        exit;
    } else {
        // ❌ Show error message
        echo "<p style='color:red;'>OTP Error: " . htmlspecialchars($result['message']) . "</p>";
        echo "<a href='../otp_page.php'>Try Again</a>";
    }
}
?>