<?php
require_once __DIR__ . '/../../config/config.php'; // defines $pdo

header('Content-Type: application/json');

// --- Safety check ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

// --- Collect inputs ---
$patient_id       = intval($_POST['patient_id'] ?? 0);
$staff_id         = intval($_POST['staff_id'] ?? 0);
$examiner         = trim($_POST['examiner'] ?? '');
$doctor_note      = trim($_POST['doctor_note'] ?? '');
$uterine_depth_cm = !empty($_POST['uterine_depth_cm']) ? floatval($_POST['uterine_depth_cm']) : null;

// --- Normalize exam_date ---
$rawDate = $_POST['exam_date'] ?? date('Y-m-d');
$normalizedDateOnly = date('Y-m-d', strtotime($rawDate));
$normalizedExamDate = $normalizedDateOnly . ' 00:00:00';

// --- Helper: checkbox parser ---
function checked($field, $value) {
    return (isset($_POST[$field]) && is_array($_POST[$field]) && in_array($value, $_POST[$field])) ? 1 : 0;
}

// --- Check for existing record with same patient and same calendar day ---
$checkSql = "SELECT record_id FROM physical_examination_record WHERE patient_id = :pid AND DATE(exam_date) = :exam_day LIMIT 1";
$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute([':pid' => $patient_id, ':exam_day' => $normalizedDateOnly]);
$existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

// --- Shared parameters ---
$params = [
    ':patient_id'       => $patient_id,
    ':staff_id'         => $staff_id,
    ':doctor_note'      => $doctor_note,
    ':examiner'         => $examiner,
    ':uterine_depth_cm' => $uterine_depth_cm,

    // Conjunctiva
    ':conjunctiva_pale'      => checked('conjunctiva', 'Pale'),
    ':conjunctiva_yellowish' => checked('conjunctiva', 'Yellowish'),

    // Neck
    ':neck_enlarged_thyroid'     => checked('neck', 'Thyroid'),
    ':neck_enlarged_lymph_nodes' => checked('neck', 'Nodes'),

    // Breast Left
    ':breast_left_mass'             => checked('breast_left', 'mass'),
    ':breast_left_nipple_discharge' => checked('breast_left', 'nipple'),
    ':breast_left_skin_dimpling'    => checked('breast_left', 'skin'),
    ':breast_left_axillary_nodes'   => checked('breast_left', 'axillary'),

    // Breast Right
    ':breast_right_mass'             => checked('breast_right', 'mass'),
    ':breast_right_nipple_discharge' => checked('breast_right', 'nipple'),
    ':breast_right_skin_dimpling'    => checked('breast_right', 'skin'),
    ':breast_right_axillary_nodes'   => checked('breast_right', 'axillary'),

    // Thorax
    ':thorax_abnormal_heart_sounds' => checked('thorax', 'heart'),
    ':thorax_abnormal_breath_sounds'=> checked('thorax', 'breath'),

    // Abdomen
    ':abdomen_enlarged_liver' => checked('abdomen', 'liver'),
    ':abdomen_mass'           => checked('abdomen', 'mass'),
    ':abdomen_tenderness'     => checked('abdomen', 'tenderness'),

    // Extremities
    ':extremities_edema'        => checked('extremities', 'edema'),
    ':extremities_varicosities' => checked('extremities', 'varicosities'),

    // Cervix / Uterus
    ':cervix_consistency' => $_POST['cervix_consistency'] ?? null,
    ':uterus_position'    => $_POST['uterus_position'] ?? null,
    ':uterus_size'        => $_POST['uterus_size'] ?? null,

    // Adnexa
    ':adnexa_mass'       => checked('adnexa', 'mass'),
    ':adnexa_tenderness' => checked('adnexa', 'tenderness')
];
try {
    if ($existing) {
        // --- Update existing record by patient_id and exam_date ---
        $params[':exam_date'] = $normalizedExamDate;

        $updateSql = "UPDATE physical_examination_record SET
            staff_id = :staff_id,
            doctor_note = :doctor_note,
            examiner = :examiner,
            uterine_depth_cm = :uterine_depth_cm,
            conjunctiva_pale = :conjunctiva_pale,
            conjunctiva_yellowish = :conjunctiva_yellowish,
            neck_enlarged_thyroid = :neck_enlarged_thyroid,
            neck_enlarged_lymph_nodes = :neck_enlarged_lymph_nodes,
            breast_left_mass = :breast_left_mass,
            breast_left_nipple_discharge = :breast_left_nipple_discharge,
            breast_left_skin_dimpling = :breast_left_skin_dimpling,
            breast_left_axillary_nodes = :breast_left_axillary_nodes,
            breast_right_mass = :breast_right_mass,
            breast_right_nipple_discharge = :breast_right_nipple_discharge,
            breast_right_skin_dimpling = :breast_right_skin_dimpling,
            breast_right_axillary_nodes = :breast_right_axillary_nodes,
            thorax_abnormal_heart_sounds = :thorax_abnormal_heart_sounds,
            thorax_abnormal_breath_sounds = :thorax_abnormal_breath_sounds,
            abdomen_enlarged_liver = :abdomen_enlarged_liver,
            abdomen_mass = :abdomen_mass,
            abdomen_tenderness = :abdomen_tenderness,
            extremities_edema = :extremities_edema,
            extremities_varicosities = :extremities_varicosities,
            cervix_consistency = :cervix_consistency,
            uterus_position = :uterus_position,
            uterus_size = :uterus_size,
            adnexa_mass = :adnexa_mass,
            adnexa_tenderness = :adnexa_tenderness
        WHERE patient_id = :patient_id AND DATE(exam_date) = DATE(:exam_date)";

        $stmt = $pdo->prepare($updateSql);
        $stmt->execute($params);
        echo json_encode(["success" => true, "updated" => true]);
    } else {
        // --- Insert new record ---
        $params[':exam_date'] = $normalizedExamDate;

        $insertSql = "INSERT INTO physical_examination_record (
            patient_id, staff_id, doctor_note, examiner, uterine_depth_cm,
            conjunctiva_pale, conjunctiva_yellowish,
            neck_enlarged_thyroid, neck_enlarged_lymph_nodes,
            breast_left_mass, breast_left_nipple_discharge, breast_left_skin_dimpling, breast_left_axillary_nodes,
            breast_right_mass, breast_right_nipple_discharge, breast_right_skin_dimpling, breast_right_axillary_nodes,
            thorax_abnormal_heart_sounds, thorax_abnormal_breath_sounds,
            abdomen_enlarged_liver, abdomen_mass, abdomen_tenderness,
            extremities_edema, extremities_varicosities,
            cervix_consistency, uterus_position, uterus_size,
            adnexa_mass, adnexa_tenderness,
            exam_date
        ) VALUES (
            :patient_id, :staff_id, :doctor_note, :examiner, :uterine_depth_cm,
            :conjunctiva_pale, :conjunctiva_yellowish,
            :neck_enlarged_thyroid, :neck_enlarged_lymph_nodes,
            :breast_left_mass, :breast_left_nipple_discharge, :breast_left_skin_dimpling, :breast_left_axillary_nodes,
            :breast_right_mass, :breast_right_nipple_discharge, :breast_right_skin_dimpling, :breast_right_axillary_nodes,
            :thorax_abnormal_heart_sounds, :thorax_abnormal_breath_sounds,
            :abdomen_enlarged_liver, :abdomen_mass, :abdomen_tenderness,
            :extremities_edema, :extremities_varicosities,
            :cervix_consistency, :uterus_position, :uterus_size,
            :adnexa_mass, :adnexa_tenderness,
            :exam_date
        )";

        $stmt = $pdo->prepare($insertSql);
        $stmt->execute($params);
        echo json_encode(["success" => true, "inserted" => true, "record_id" => $pdo->lastInsertId()]);
    }
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}