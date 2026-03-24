<?php
session_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Check session and role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'clerk'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../../config/config.php';

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection not initialized'
    ]);
    exit;
}

// Validate input
$patientId = $_POST['patient_id'] ?? null;
$statusLabel = $_POST['status_label'] ?? null;
$remarks = $_POST['remarks'] ?? null;
$clerkId = $_SESSION['user_id'];

if (!$patientId || !$statusLabel) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing patient_id or status_label'
    ]);
    exit;
}

try {
    // Ensure status_reference table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS status_reference (
            label_id INT AUTO_INCREMENT PRIMARY KEY,
            status_label VARCHAR(50) UNIQUE NOT NULL,
            description TEXT
        )
    ");

    // Ensure patient_status table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS patient_status (
            status_id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            status_label_id INT NOT NULL,
            updated_by INT NOT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            remarks TEXT,
            FOREIGN KEY (patient_id) REFERENCES patient_records(patient_id),
            FOREIGN KEY (status_label_id) REFERENCES status_reference(label_id),
            FOREIGN KEY (updated_by) REFERENCES users(user_id)
        )
    ");

    // Check if status_label exists in reference table
    $stmt = $pdo->prepare("SELECT label_id FROM status_reference WHERE status_label = ?");
    $stmt->execute([$statusLabel]);
    $label = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$label) {
        // Insert new status label
        $stmt = $pdo->prepare("INSERT INTO status_reference (status_label) VALUES (?)");
        $stmt->execute([$statusLabel]);
        $labelId = $pdo->lastInsertId();
    } else {
        $labelId = $label['label_id'];
    }

    // Insert new status update (preserves audit trail)
    $stmt = $pdo->prepare("
        INSERT INTO patient_status (patient_id, status_label_id, updated_by, remarks)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$patientId, $labelId, $clerkId, $remarks]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Patient status updated successfully'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}