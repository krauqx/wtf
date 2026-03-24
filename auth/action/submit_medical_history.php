<?php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

$patient_id = intval($_POST['patient_id'] ?? 0);
$visit_date = $_POST['visit_date'] ?? date('Y-m-d');
$staff_id = intval($_POST['staff_id'] ?? 0);
$doctor_note = $_POST['doctor_note'] ?? '';

// Helper: checkbox flag
function checked($group, $value) {
    return (isset($_POST[$group]) && is_array($_POST[$group]) && in_array($value, $_POST[$group])) ? 1 : 0;
}

// Helper: notes field
function notes($name) {
    return trim($_POST[$name] ?? '');
}

$sql = "INSERT INTO medical_history (
    patient_id, visit_date, staff_id, doctor_note,

    epilepsy_seizure, epilepsy_notes,
    severe_headache_dizziness, headache_notes,
    visual_disturbance, vision_notes,
    yellowish_conjunctivitis, conjunctivitis_notes,
    enlarged_thyroid, thyroid_notes,

    severe_chest_pain, chest_pain_notes,
    shortness_of_breath, shortness_breath_notes,
    breast_axillary_masses, breast_mass_notes,
    nipple_discharge, nipple_discharge_notes,
    systolic_140_above, systolic_notes,
    diastolic_90_above, diastolic_notes,
    history_cva, family_history_notes,

    abdominal_mass, abdomen_mass_notes,
    history_gallbladder_disease, gallbladder_notes,
    history_liver_disease, liver_notes,

    uterine_mass, uterine_mass_notes,
    vaginal_discharge, vaginal_discharge_notes,
    intermenstrual_bleeding, intermenstrual_bleeding_notes,
    postcoital_bleeding, postcoital_bleeding_notes,

    severe_varicosities, varicosities_notes,
    leg_swelling_pain, leg_pain_notes,

    yellowish_skin, yellowish_skin_notes,

    history_smoking, smoking_notes,
    history_allergies, allergies_notes,
    history_drug_intake, drug_intake_notes,
    history_std, std_notes,
    history_multiple_partners, multiple_partners_notes,
    bleeding_tendencies, bleeding_tendencies_notes,
    history_anemia, anemia_notes,
    history_diabetes, diabetes_notes,

    sti_multiple_partners, sti_multiple_partners_notes,
    sti_women_discharge, sti_women_discharge_notes,
    sti_women_itching_sores, sti_women_itching_notes,
    sti_women_pain_burning, sti_women_pain_notes,
    sti_women_treated_sti, sti_women_treated_notes,
    sti_men_pain_burning, sti_men_pain_notes,
    sti_men_open_sores, sti_men_sores_notes,
    sti_men_pus_penis, sti_men_pus_notes,
    sti_men_swollen_genitals, sti_men_swollen_notes,
    sti_men_treated_sti, sti_men_treated_notes
) VALUES (
    :patient_id, :visit_date, :staff_id, :doctor_note,

    :epilepsy_seizure, :epilepsy_notes,
    :severe_headache_dizziness, :headache_notes,
    :visual_disturbance, :vision_notes,
    :yellowish_conjunctivitis, :conjunctivitis_notes,
    :enlarged_thyroid, :thyroid_notes,

    :severe_chest_pain, :chest_pain_notes,
    :shortness_of_breath, :shortness_breath_notes,
    :breast_axillary_masses, :breast_mass_notes,
    :nipple_discharge, :nipple_discharge_notes,
    :systolic_140_above, :systolic_notes,
    :diastolic_90_above, :diastolic_notes,
    :history_cva, :family_history_notes,

    :abdominal_mass, :abdomen_mass_notes,
    :history_gallbladder_disease, :gallbladder_notes,
    :history_liver_disease, :liver_notes,

    :uterine_mass, :uterine_mass_notes,
    :vaginal_discharge, :vaginal_discharge_notes,
    :intermenstrual_bleeding, :intermenstrual_bleeding_notes,
    :postcoital_bleeding, :postcoital_bleeding_notes,

    :severe_varicosities, :varicosities_notes,
    :leg_swelling_pain, :leg_pain_notes,

    :yellowish_skin, :yellowish_skin_notes,

    :history_smoking, :smoking_notes,
    :history_allergies, :allergies_notes,
    :history_drug_intake, :drug_intake_notes,
    :history_std, :std_notes,
    :history_multiple_partners, :multiple_partners_notes,
    :bleeding_tendencies, :bleeding_tendencies_notes,
    :history_anemia, :anemia_notes,
    :history_diabetes, :diabetes_notes,

    :sti_multiple_partners, :sti_multiple_partners_notes,
    :sti_women_discharge, :sti_women_discharge_notes,
    :sti_women_itching_sores, :sti_women_itching_notes,
    :sti_women_pain_burning, :sti_women_pain_notes,
    :sti_women_treated_sti, :sti_women_treated_notes,
    :sti_men_pain_burning, :sti_men_pain_notes,
    :sti_men_open_sores, :sti_men_sores_notes,
    :sti_men_pus_penis, :sti_men_pus_notes,
    :sti_men_swollen_genitals, :sti_men_swollen_notes,
    :sti_men_treated_sti, :sti_men_treated_notes
)";

$stmt = $pdo->prepare($sql);
$params = [
    ':patient_id' => $patient_id,
    ':visit_date' => $visit_date,
    ':staff_id' => $staff_id,
    ':doctor_note' => $doctor_note,

    ':epilepsy_seizure' => checked('heent', 'epilepsy'),
    ':epilepsy_notes' => notes('epilepsy_notes'),
    ':severe_headache_dizziness' => checked('heent', 'headache'),
    ':headache_notes' => notes('headache_notes'),
    ':visual_disturbance' => checked('heent', 'vision'),
    ':vision_notes' => notes('vision_notes'),
    ':yellowish_conjunctivitis' => checked('heent', 'conjunctivitis'),
    ':conjunctivitis_notes' => notes('conjunctivitis_notes'),
    ':enlarged_thyroid' => checked('heent', 'thyroid'),
    ':thyroid_notes' => notes('thyroid_notes'),

    ':severe_chest_pain' => checked('chest_heart', 'chest_pain'),
    ':chest_pain_notes' => notes('chest_pain_notes'),
    ':shortness_of_breath' => checked('chest_heart', 'shortness_breath'),
    ':shortness_breath_notes' => notes('shortness_breath_notes'),
    ':breast_axillary_masses' => checked('chest_heart', 'breast_mass'),
    ':breast_mass_notes' => notes('breast_mass_notes'),
    ':nipple_discharge' => checked('chest_heart', 'nipple_discharge'),
    ':nipple_discharge_notes' => notes('nipple_discharge_notes'),
    ':systolic_140_above' => checked('chest_heart', 'systolic'),
    ':systolic_notes' => notes('systolic_notes'),
    ':diastolic_90_above' => checked('chest_heart', 'diastolic'),
    ':diastolic_notes' => notes('diastolic_notes'),
    ':history_cva' => checked('chest_heart', 'family_history'),
    ':family_history_notes' => notes('family_history_notes'),

    ':abdominal_mass' => checked('abdomen', 'mass'),
    ':abdomen_mass_notes' => notes('abdomen_mass_notes'),
    ':history_gallbladder_disease' => checked('abdomen', 'gallbladder'),
    ':gallbladder_notes' => notes('gallbladder_notes'),
    ':history_liver_disease' => checked('abdomen', 'liver'),
    ':liver_notes' => notes('liver_notes'),

    ':uterine_mass' => checked('genital', 'uterine_mass'),
    ':uterine_mass_notes' => notes('uterine_mass_notes'),
    ':vaginal_discharge' => checked('genital', 'vaginal_discharge'),
    ':vaginal_discharge_notes' => notes('vaginal_discharge_notes'),
    ':intermenstrual_bleeding' => checked('genital', 'intermenstrual_bleeding'),
    ':intermenstrual_bleeding_notes' => notes('intermenstrual_bleeding_notes'),
    ':postcoital_bleeding' => checked('genital', 'postcoital_bleeding'),
    ':postcoital_bleeding_notes' => notes('postcoital_bleeding_notes'),

    ':severe_varicosities' => checked('extremities', 'varicosities'),
    ':varicosities_notes' => notes('varicosities_notes'),
    ':leg_swelling_pain' => checked('extremities', 'leg_pain'),
    ':leg_pain_notes' => notes('leg_pain_notes'),

    ':yellowish_skin' => checked('skin', 'yellowish_skin'),
    ':yellowish_skin_notes' => notes('yellowish_skin_notes'),

    ':history_smoking' => checked('history', 'smoking'),
    ':smoking_notes' => notes('smoking_notes'),
    ':history_allergies' => checked('history', 'allergies'),
    ':allergies_notes' => notes('allergies_notes'),
    ':history_drug_intake' => checked('history', 'drug_intake'),
    ':drug_intake_notes' => notes('drug_intake_notes'),
    ':history_std' => checked('history', 'std'),
      ':std_notes' => notes('std_notes'),
    ':history_multiple_partners' => checked('history', 'multiple_partners'),
    ':multiple_partners_notes' => notes('multiple_partners_notes'),
    ':bleeding_tendencies' => checked('history', 'bleeding_tendencies'),
    ':bleeding_tendencies_notes' => notes('bleeding_tendencies_notes'),
    ':history_anemia' => checked('history', 'anemia'),
    ':anemia_notes' => notes('anemia_notes'),
    ':history_diabetes' => checked('history', 'diabetes'),
    ':diabetes_notes' => notes('diabetes_notes'),

    // STI RISKS
    ':sti_multiple_partners' => checked('sti_risks', 'multiple_partners'),
    ':sti_multiple_partners_notes' => notes('sti_multiple_partners_notes'),

    // For Women
    ':sti_women_discharge' => checked('sti_women', 'unusual_discharge'),
    ':sti_women_discharge_notes' => notes('sti_women_discharge_notes'),
    ':sti_women_itching_sores' => checked('sti_women', 'itching_sores'),
    ':sti_women_itching_notes' => notes('sti_women_itching_notes'),
    ':sti_women_pain_burning' => checked('sti_women', 'pain_burning'),
    ':sti_women_pain_notes' => notes('sti_women_pain_notes'),
    ':sti_women_treated_sti' => checked('sti_women', 'treated_sti'),
    ':sti_women_treated_notes' => notes('sti_women_treated_notes'),

    // For Men
    ':sti_men_pain_burning' => checked('sti_men', 'pain_burning'),
    ':sti_men_pain_notes' => notes('sti_men_pain_notes'),
    ':sti_men_open_sores' => checked('sti_men', 'open_sores'),
    ':sti_men_sores_notes' => notes('sti_men_sores_notes'),
    ':sti_men_pus_penis' => checked('sti_men', 'pus_penis'),
    ':sti_men_pus_notes' => notes('sti_men_pus_notes'),
    ':sti_men_swollen_genitals' => checked('sti_men', 'swollen_genitals'),
    ':sti_men_swollen_notes' => notes('sti_men_swollen_notes'),
    ':sti_men_treated_sti' => checked('sti_men', 'treated_sti'),
    ':sti_men_treated_notes' => notes('sti_men_treated_notes')
];

// try {
    
//     $stmt->execute($params);
//     echo json_encode(["success" => true, "record_id" => $pdo->lastInsertId()]);
//     exit;
// } catch (PDOException $e) {
//     http_response_code(500);
//     echo json_encode(["error" => $e->getMessage()]);
// }
try {
    $stmt->execute($params);
    echo json_encode([
        "success" => true,
        "record_id" => $pdo->lastInsertId(),
        "debug" => [
            "raw_post" => $_POST,
            "bound_params" => $params
        ]
    ]);
    exit;
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

