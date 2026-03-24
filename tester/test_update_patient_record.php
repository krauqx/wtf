<?php
// test_update_patient_record.php
session_start();

require_once '../config/config.php';
require_once '../auth/update_patient_record.php'; // include your function file

try {
    // Connect to DB
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Hardcode test values for user_id = 1
    $user_id     = 1;
    $first_name  = "SecondTest";
    $middle_name = "M";
    $last_name   = "User";
    $dob         = "1990-01-01";
    $age         = 35;
    $gender      = "Male";
    $status      = "Single";
    $contact     = "09171234567";
    $occupation  = "Tester";
    $address     = "123 Test Street, Bacoor City";
    $patient_img = null; // or "uploads/test.png" if you want to simulate an image path

    // Call the function
    $result = update_patient_record(
        $pdo,
        $user_id,
        $first_name,
        $middle_name,
        $last_name,
        $dob,
        $age,
        $gender,
        $status,
        $contact,
        $occupation,
        $address,
        $patient_img
    );

    echo "<h3>Function Result:</h3>";
    echo "<p>$result</p>";

    // Fetch back the record to verify
    $stmt = $pdo->prepare("SELECT * FROM patient_records WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h3>Database Record for user_id=1:</h3>";
    echo "<pre>" . print_r($record, true) . "</pre>";

} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}