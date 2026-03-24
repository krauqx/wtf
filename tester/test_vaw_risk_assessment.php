<?php
session_start();
$_SESSION['patient_id'] = 1; // Replace with a valid patient ID from your DB
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>VAW Risk Assessment Tester</title>
  <style>
    body { font-family: sans-serif; padding: 40px; background: #f9fafb; }
    label { display: block; margin-top: 16px; font-weight: bold; }
    input[type="text"], textarea {
      width: 100%; padding: 10px; margin-top: 4px;
      border: 1px solid #ccc; border-radius: 6px;
    }
    .checkbox-group {
      margin-top: 24px; padding: 16px;
      background: #fff; border: 1px solid #ddd; border-radius: 8px;
    }
    .checkbox-group label {
      font-weight: normal; margin-bottom: 8px; display: block;
    }
    button {
      margin-top: 32px; padding: 12px 24px; font-weight: bold;
      background: #4f46e5; color: white; border: none;
      border-radius: 8px; cursor: pointer;
    }
  </style>
</head>
<body>

  <h2>Test VAW Risk Assessment Submission</h2>

  <form action="/JAM_LYINGIN/auth/action/submit_vaw_risk_assessment.php" method="POST">
    <input type="hidden" name="visit_date" value="2025-10-15">

    <div class="checkbox-group">
      <h3>Risk Indicators</h3>

      <label>
        <input type="checkbox" name="vaw_risk[]" value="domestic_violence">
        History of Domestic Violence or VAW
      </label>
      <input type="text" name="domestic_violence_notes" placeholder="Note (optional)">

      <label>
        <input type="checkbox" name="vaw_risk[]" value="unpleasant_relationship">
        Unpleasant Relationship with Partner
      </label>
      <input type="text" name="unpleasant_relationship_notes" placeholder="Note (optional)">

      <label>
        <input type="checkbox" name="vaw_risk[]" value="partner_disapproves_visit">
        Partner Does Not Approve Of The Visit to FP Clinic
      </label>
      <input type="text" name="partner_disapproves_visit_notes" placeholder="Note (optional)">

      <label>
        <input type="checkbox" name="vaw_risk[]" value="partner_disagrees_fp">
        Partner Disagrees to Use FP
      </label>
      <input type="text" name="partner_disagrees_fp_notes" placeholder="Note (optional)">
    </div>

    <div class="checkbox-group">
      <h3>Referred To</h3>

      <label>
        <input type="checkbox" name="referred_to[]" value="dswd">
        DSWD
      </label>

      <label>
        <input type="checkbox" name="referred_to[]" value="wcpu">
        WCPU
      </label>

      <label>
        <input type="checkbox" name="referred_to[]" value="ngos">
        NGOs
      </label>

      <label>
        <input type="checkbox" name="referred_to[]" value="others">
        Others (specify)
      </label>
      <input type="text" name="others_specify_notes" placeholder="Specify organization (required if checked)">
    </div>

    <button type="submit">Submit Assessment</button>
  </form>

</body>
</html>