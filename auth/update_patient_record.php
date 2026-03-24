<?php
// update_patient_record.php
// FUNCTION PHP.
require_once '../config/config.php';
include_once '../url.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

function update_patient_record(
    $pdo, $user_id,
    $first_name, $middle_name, $last_name,
    $dob, $age, $gender, $status, $contact,
    $occupation, $address, $patient_image = null,
    $emergency_name = null, $emergency_contact = null,
    $emergency_address = null, $relationship = null
) {
    try {
        // Check if patient record already exists
        $stmt = $pdo->prepare("SELECT patient_id FROM patient_records WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing record
            $sql = "UPDATE patient_records 
                    SET first_name = ?, middle_name = ?, last_name = ?, date_of_birth = ?, age = ?, 
                        gender = ?, status = ?, contact_number = ?, occupation = ?, address = ?, patient_image = ?, 
                        emergency_name = ?, emergency_contact = ?, emergency_address = ?, relationship = ?, 
                        updated_at = NOW()
                    WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $first_name, $middle_name, $last_name, $dob, $age,
                $gender, $status, $contact, $occupation, $address, $patient_image,
                $emergency_name, $emergency_contact, $emergency_address, $relationship,
                $user_id
            ]);
            return "✅ Patient record updated for user_id=$user_id.";
        } else {
            // Insert new record
            $sql = "INSERT INTO patient_records 
                    (user_id, first_name, middle_name, last_name, date_of_birth, age, gender, status, 
                     contact_number, occupation, address, patient_image, emergency_name, emergency_contact, emergency_address, relationship) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $user_id, $first_name, $middle_name, $last_name, $dob, $age,
                $gender, $status, $contact, $occupation, $address, $patient_image,
                $emergency_name, $emergency_contact, $emergency_address, $relationship
            ]);
            return "✅ New patient record created for user_id=$user_id.";
        }
    } catch (PDOException $e) {
        return "❌ Database error: " . $e->getMessage();
    }
}
