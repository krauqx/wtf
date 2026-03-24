<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/config.php'; 

// Validate session-bound patient ID


$patientId = intval($_POST['patient_id'] ?? 0);
$visitDate = $_POST['visit_date'] ?? null;
$staffId   = isset($_POST['staff_id']) && is_numeric($_POST['staff_id']) ? (int) $_POST['staff_id'] : 0;
$doctorNote = trim($_POST['doctor_note'] ?? '');

// Validate visit date format (YYYY-MM-DD)
if (!$visitDate) {
    $phTime = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $visitDate = $phTime->format('Y-m-d');
}



// Risk flags
$riskFlags = [
    'domestic_violence'        => 0,
    'unpleasant_relationship'  => 0,
    'partner_disapproves_visit'=> 0,
    'partner_disagrees_fp'     => 0
];

$riskNotes = [
    'domestic_violence'        => $_POST['domestic_violence_notes'] ?? null,
    'unpleasant_relationship'  => $_POST['unpleasant_relationship_notes'] ?? null,
    'partner_disapproves_visit'=> $_POST['partner_disapproves_visit_notes'] ?? null,
    'partner_disagrees_fp'     => $_POST['partner_disagrees_fp_notes'] ?? null
];

if (!empty($_POST['vaw_risk']) && is_array($_POST['vaw_risk'])) {
    foreach ($_POST['vaw_risk'] as $risk) {
        if (isset($riskFlags[$risk])) {
            $riskFlags[$risk] = 1;
        }
    }
}

// Referral flags
$referralFlags = [
    'dswd'   => 0,
    'wcpu'   => 0,
    'ngos'   => 0,
    'others' => 0
];

$otherReferralNote = null;

if (!empty($_POST['referred_to']) && is_array($_POST['referred_to'])) {
    foreach ($_POST['referred_to'] as $ref) {
        if (isset($referralFlags[$ref])) {
            $referralFlags[$ref] = 1;
            if ($ref === 'others') {
                $otherReferralNote = $_POST['others_specify_notes'] ?? null;
            }
        }
    }
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO vaw_risk_assessment (
            patient_id, staff_id, visit_date, doctor_note,
            history_domestic_violence, note_history_domestic_violence,
            unpleasant_relationship, note_unpleasant_relationship,
            partner_disapproves_visit, note_partner_disapproves_visit,
            partner_disagrees_fp, note_partner_disagrees_fp,
            referred_to_dswd, referred_to_wcpu, referred_to_ngos,
            referred_to_others, other_referral_note
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $patientId,
        $staffId,
        $visitDate,
        $doctorNote,
        $riskFlags['domestic_violence'],
        $riskNotes['domestic_violence'],
        $riskFlags['unpleasant_relationship'],
        $riskNotes['unpleasant_relationship'],
        $riskFlags['partner_disapproves_visit'],
        $riskNotes['partner_disapproves_visit'],
        $riskFlags['partner_disagrees_fp'],
        $riskNotes['partner_disagrees_fp'],
        $referralFlags['dswd'],
        $referralFlags['wcpu'],
        $referralFlags['ngos'],
        $referralFlags['others'],
        $otherReferralNote
    ]);

    echo json_encode(['success' => true]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}