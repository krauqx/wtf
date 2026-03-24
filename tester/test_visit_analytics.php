<?php
session_start();
$_SESSION['patient_id'] = 1; // Replace with a valid patient ID from your DB
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Visit Analytics Tester</title>
  <style>
    body { font-family: sans-serif; padding: 40px; background: #f9fafb; }
    label { display: block; margin-top: 16px; font-weight: bold; }
    input, textarea { width: 100%; padding: 10px; margin-top: 4px; border: 1px solid #ccc; border-radius: 6px; }
    button { margin-top: 24px; padding: 12px 24px; font-weight: bold; background: #4f46e5; color: white; border: none; border-radius: 8px; cursor: pointer; }
  </style>
</head>
<body>

  <h2>Test Visit Analytics Submission</h2>

  <form action="/JAM_LYINGIN/auth/action/submit_visit_analytics.php" method="POST">
    <input type="hidden" name="visit_date" value="2025-10-15">

    <label for="bp">Blood Pressure</label>
    <input type="text" name="bp" id="bp" value="120/80" required>

    <label for="temp">Temperature (°F)</label>
    <input type="number" step="0.01" name="temp" id="temp" value="98.60" required>

    <label for="weight">Weight (kg)</label>
    <input type="number" step="0.01" name="weight" id="weight" value="65.00" required>

    <label for="fundal_height">Fundal Height (cm)</label>
    <input type="number" step="0.01" name="fundal_height" id="fundal_height" value="24.00" required>

    <label for="fetal_heart_tone">Fetal Heart Tone (bpm)</label>
    <input type="number" name="fetal_heart_tone" id="fetal_heart_tone" value="140" required>

    <label for="fetal_position">Fetal Position</label>
    <input type="text" name="fetal_position" id="fetal_position" value="Cephalic" required>

    <label for="chief_complaint">Chief Complaint</label>
    <textarea name="chief_complaint" id="chief_complaint" required>Routine prenatal checkup</textarea>

    <button type="submit">Submit Test Data</button>
  </form>

</body>
</html>