<?php
// tester/test_update_user.php
session_start();

// Simulate a logged-in user with id=1
$_SESSION['user_id'] = 1;
$TEST_MODE = true;
// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';

// Fetch current values from DB first
require_once '../config/config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT first_name, last_name, email FROM users WHERE id=1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "❌ No user with id=1 found.";
        exit;
    }

    // Decide toggle values
    if ($user['first_name'] === 'user1' && $user['last_name'] === 'user1' && $user['email'] === 'user1@user1') {
        
        $_POST['first_name'] = 'user2';
        $_POST['last_name']  = 'user2';
        $_POST['email']      = 'user2@user2';
        echo "ℹ️ Current values are user1 → will update to user2...<br>";
    } else {
        $_POST['first_name'] = 'user1';
        $_POST['last_name']  = 'user1';
        $_POST['email']      = 'user1@user1';
        echo "ℹ️ Current values are not user1 → will update to user1...<br>";
    }

    // Now include the actual update logic
    ob_start(); // capture output from update_user.php
    include '../auth/update_user.php';
    ob_end_clean(); // discard redirect headers

    echo "✅ Update script executed successfully.<br>";

    // Verify new values
    $stmt = $pdo->query("SELECT first_name, last_name, email FROM users WHERE id=1");
    $updated = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "🔎 User after update: " . implode(", ", $updated);

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage();
}