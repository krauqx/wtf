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

$search = trim($_GET['q'] ?? '');

try {
    $sql = "
        SELECT 
            pr.patient_id,
            pr.first_name,
            pr.last_name,
            pr.age,
            pr.gender,
            COALESCE(sr.status_label, 'No Status') AS status_label,
            ps.updated_at AS status_updated_at
        FROM patient_records pr
        LEFT JOIN (
            SELECT ps1.*
            FROM patient_status ps1
            INNER JOIN (
                SELECT patient_id, MAX(updated_at) AS latest
                FROM patient_status
                GROUP BY patient_id
            ) ps2 ON ps1.patient_id = ps2.patient_id AND ps1.updated_at = ps2.latest
        ) ps ON pr.patient_id = ps.patient_id
        LEFT JOIN status_reference sr ON ps.status_label_id = sr.label_id
        WHERE (:search = '' 
            OR pr.patient_id LIKE :likeSearch
            OR pr.first_name LIKE :likeSearch
            OR pr.last_name LIKE :likeSearch
        )
        ORDER BY pr.last_name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':search' => $search,
        ':likeSearch' => "%$search%"
    ]);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $patients,
        'message' => count($patients) ? null : 'No patient records found'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDO error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}