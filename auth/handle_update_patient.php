<?php
// handle_update_patient.php
// handler php
session_start();
require_once '../config/config.php';
require_once 'update_patient_record.php'; // this is your function file
require_once '../config/url.php';
require_once '../url.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id     = $_SESSION['user_id'] ?? 1; // fallback for testing
        $first_name  = $_POST['firstName'] ?? null;
        $middle_name = $_POST['middleName'] ?? null;
        $last_name   = $_POST['lastName'] ?? null;
        $dob         = $_POST['dob'] ?? null;
        $age         = $_POST['age'] ?? null;
        $gender      = $_POST['gender'] ?? null;
        $status      = $_POST['status'] ?? null;
        $contact     = $_POST['contact'] ?? null;
        $occupation  = $_POST['occupation'] ?? null;
        $address     = $_POST['address'] ?? null;
        $emergency_name     = $_POST['emergencyName'] ?? null;
        $emergency_contact  = $_POST['emergencyContact'] ?? null;
        $emergency_address  = $_POST['emergencyAddress'] ?? null;
        $relationship       = $_POST['relationship'] ?? null;

        // File upload
        $patient_image = null;
        if (!empty($_FILES['patientImage']['name'])) {
            $uploadDir = "../uploads/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . "_" . basename($_FILES['patientImage']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['patientImage']['tmp_name'], $targetFile)) {
                $patient_image = "uploads/" . $fileName;
            }
        }

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
            $patient_image,
            $emergency_name,
            $emergency_contact,
            $emergency_address,
            $relationship

        );

        header("Location: " . URL_DASH_PATIENT);
        exit();
    }
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage();
}