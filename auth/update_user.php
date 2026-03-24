<?php
// update_user.php
// FUNCTION PHP.
session_start();
require '../config/config.php';
require_once '../url.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id    = $_SESSION['user_id'];
        $first_name = trim($_POST['first_name']);
        $last_name  = trim($_POST['last_name']);
        $email      = trim($_POST['email']);

        $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?");
        $stmt->execute([$first_name, $last_name, $email, $user_id]);

        // Redirect to dashboard after update
       if (!empty($TEST_MODE)) {
        echo "✅ Update successful for user_id=$user_id → $first_name $last_name ($email)";
        exit;
    } else {
        header("Location: " . URL_DASH_PATIENT);
        exit;
    }

        exit();
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
