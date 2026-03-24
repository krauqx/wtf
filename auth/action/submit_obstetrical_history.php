<?php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

$patient_id = intval($_POST['patient_id'] ?? 0);
$visit_date = date('Y-m-d', strtotime($_POST['visit_date'] ?? date('Y-m-d')));
$staff_id   = intval($_POST['staff_id'] ?? 0);
$doctor_note = trim($_POST['doctor_note'] ?? '');

// Helpers
function checked($group, $value) {
    return (isset($_POST[$group]) && is_array($_POST[$group]) && in_array($value, $_POST[$group])) ? 1 : 0;
}
function notes($name) {
    return trim($_POST[$name] ?? '');
}

$params = [
    ':patient_id' => $patient_id,
    ':visit_date' => $visit_date,
    ':staff_id' => $staff_id,
    ':doctor_note' => $doctor_note,
    ':full_term' => intval($_POST['full_term'] ?? 0),
    ':premature' => intval($_POST['premature'] ?? 0),
    ':abortions' => intval($_POST['abortions'] ?? 0),
    ':living_children' => intval($_POST['living_children'] ?? 0),
    ':last_delivery_date' => $_POST['last_delivery_date'] ?? null,
    ':last_delivery_type' => $_POST['last_delivery_type'] ?? null,
    ':past_menstrual_period' => $_POST['past_menstrual_period'] ?? null,
    ':menstrual_character' => $_POST['menstrual_character'] ?? null,
    ':hydatidiform_mole' => checked('obstetrical_history', 'hydatidiform_mole'),
    ':hydatidiform_mole_notes' => notes('hydatidiform_mole_notes'),
    ':ectopic_pregnancy' => checked('obstetrical_history', 'ectopic_pregnancy'),
    ':ectopic_pregnancy_notes' => notes('ectopic_pregnancy_notes')
];

// Check for existing record
$checkSql = "SELECT id FROM obstetrical_history WHERE patient_id = :pid AND visit_date = :vdate LIMIT 1";
$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute([':pid' => $patient_id, ':vdate' => $visit_date]);
$existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

// Build SQL
if ($existing) {
    $updateFields = [];
    foreach ($params as $key => $value) {
        if ($key !== ':patient_id' && $key !== ':visit_date') {
            $updateFields[] = substr($key, 1) . " = $key";
        }
    }

    $sql = "UPDATE obstetrical_history SET " . implode(", ", $updateFields) .
           " WHERE patient_id = :patient_id AND visit_date = :visit_date";
} else {
    $sql = "INSERT INTO obstetrical_history (
        patient_id, visit_date, staff_id, doctor_note,
        full_term, premature, abortions, living_children,
        last_delivery_date, last_delivery_type, past_menstrual_period, menstrual_character,
        hydatidiform_mole, hydatidiform_mole_notes,
        ectopic_pregnancy, ectopic_pregnancy_notes
    ) VALUES (
        :patient_id, :visit_date, :staff_id, :doctor_note,
        :full_term, :premature, :abortions, :living_children,
        :last_delivery_date, :last_delivery_type, :past_menstrual_period, :menstrual_character,
        :hydatidiform_mole, :hydatidiform_mole_notes,
        :ectopic_pregnancy, :ectopic_pregnancy_notes
    )";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        "success" => true,
        $existing ? "updated" : "inserted" => true,
        "record_id" => $existing ? $existing['id'] : $pdo->lastInsertId(),
        "debug" => [
            "raw_post" => $_POST,
            "bound_params" => $params
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => $e->getMessage(),
        "debug" => [
            "raw_post" => $_POST,
            "bound_params" => $params
        ]
    ]);
}