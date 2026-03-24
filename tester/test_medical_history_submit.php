<?php
include_once __DIR__ . '/../config/config.php';

// Simulate a POST request to the medical history submission endpoint
$url = 'http://localhost/JAM_LYINGIN/auth/action/submit_medical_history.php'; // Adjust path as needed

$data = [
    'patient_id' => 1,
    'visit_date' => '2025-10-15',

    // HEENT
    'heent' => ['epilepsy', 'headache', 'vision', 'conjunctivitis', 'thyroid'],
    'epilepsy_notes' => 'Occasional seizures since childhood',
    'headache_notes' => 'Frequent migraines',
    'vision_notes' => 'Blurred vision in the morning',
    'conjunctivitis_notes' => 'Mild redness observed',
    'thyroid_notes' => 'Slight enlargement noted',

    // CHEST/HEART
    'chest_heart' => ['chest_pain', 'shortness_breath', 'breast_mass', 'nipple_discharge', 'systolic', 'diastolic', 'family_history'],
    'chest_pain_notes' => 'Sharp pain during exertion',
    'shortness_breath_notes' => 'Triggered by climbing stairs',
    'breast_mass_notes' => 'Left side lump',
    'nipple_discharge_notes' => 'Clear fluid',
    'systolic_notes' => '145 mmHg last check',
    'diastolic_notes' => '92 mmHg last check',
    'family_history_notes' => 'Father had stroke at 60',

    // ABDOMEN
    'abdomen' => ['mass', 'gallbladder', 'liver'],
    'abdomen_mass_notes' => 'Palpable lump in lower right quadrant',
    'gallbladder_notes' => 'History of gallstones',
    'liver_notes' => 'Elevated enzymes noted',

    // GENITAL
    'genital' => ['uterine_mass', 'vaginal_discharge', 'intermenstrual_bleeding', 'postcoital_bleeding'],
    'uterine_mass_notes' => 'Detected via ultrasound',
    'vaginal_discharge_notes' => 'Yellowish discharge',
    'intermenstrual_bleeding_notes' => 'Occasional spotting',
    'postcoital_bleeding_notes' => 'Light bleeding after intercourse',

    // EXTREMITIES
    'extremities' => ['varicosities', 'leg_pain'],
    'varicosities_notes' => 'Visible veins on both legs',
    'leg_pain_notes' => 'Pain after prolonged standing',

    // SKIN
    'skin' => ['yellowish_skin'],
    'yellowish_skin_notes' => 'Mild jaundice observed',

    // HISTORY
    'history' => ['smoking', 'allergies', 'drug_intake', 'std', 'multiple_partners', 'bleeding_tendencies', 'anemia', 'diabetes'],
    'smoking_notes' => 'Quit 5 years ago',
    'allergies_notes' => 'Allergic to penicillin',
    'drug_intake_notes' => 'Occasional NSAIDs',
    'std_notes' => 'Treated for gonorrhea in 2020',
    'multiple_partners_notes' => '3 partners in past year',
    'bleeding_tendencies_notes' => 'Easy bruising',
    'anemia_notes' => 'Iron deficiency noted',
    'diabetes_notes' => 'Borderline fasting glucose',

    // STI RISKS
    'sti_risks' => ['multiple_partners'],
    'sti_multiple_partners_notes' => '3 partners in the past year',

    // STI - Women (corrected values)
    'sti_women' => ['unusual_discharge', 'itching_sores', 'pain_burning', 'treated_sti'],
    'sti_women_discharge_notes' => 'Thick white discharge',
    'sti_women_itching_notes' => 'Persistent itching',
    'sti_women_pain_notes' => 'Burning sensation during urination',
    'sti_women_treated_notes' => 'Completed STI treatment last month',

    // STI - Men (corrected values)
    'sti_men' => ['pain_burning', 'open_sores', 'pus_penis', 'swollen_genitals', 'treated_sti'],
    'sti_men_pain_notes' => 'Burning sensation reported',
    'sti_men_sores_notes' => 'Two open sores observed',
    'sti_men_pus_notes' => 'Yellowish discharge',
    'sti_men_swollen_notes' => 'Swelling on left testicle',
    'sti_men_treated_notes' => 'Treated with antibiotics',
];

// Use cURL to send the POST request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

// Execute and capture response
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlErrno = curl_errno($ch);
curl_close($ch);

// Decode JSON response
$decoded = json_decode($response, true);

// Output diagnostics
header('Content-Type: application/json');
echo json_encode([
    'http_code' => $httpCode,
    'curl_error' => $curlError ?: null,
    'curl_errno' => $curlErrno ?: null,
    'raw_response' => $response ?: null,
    'decoded_response' => $decoded ?: 'Invalid or empty JSON response'
], JSON_PRETTY_PRINT);