<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Test Physical Exam Submission</title>
</head>
<body>
  <h2>Test Physical Examination Submission</h2>

  <form action="../auth/action/submit_physical_exam_record.php" method="POST">
    <!-- Patient ID -->
    <label>Patient ID:
      <input type="number" name="patient_id" value="1" required>
    </label>
    <br><br>

    <!-- Examiner -->
    <label>Examiner:
      <input type="text" name="examiner" value="Dr. Tester">
    </label>
    <br><br>

    <!-- Notes -->
    <label>Notes:<br>
      <textarea name="notes" rows="3" cols="40">Sample test notes</textarea>
    </label>
    <br><br>

    <!-- Uterine Depth -->
    <label>Uterine Depth (cm):
      <input type="number" step="0.1" name="uterine_depth_cm" value="7.5">
    </label>
    <br><br>

    <!-- Conjunctiva -->
    <fieldset>
      <legend>Conjunctiva</legend>
      <label><input type="checkbox" name="conjunctiva[]" value="Pale" checked> Pale</label>
      <label><input type="checkbox" name="conjunctiva[]" value="Yellowish"> Yellowish</label>
    </fieldset>

    <!-- Neck -->
    <fieldset>
      <legend>Neck</legend>
      <label><input type="checkbox" name="neck[]" value="Thyroid"> Enlarged Thyroid</label>
      <label><input type="checkbox" name="neck[]" value="Nodes"> Enlarged Lymph Nodes</label>
    </fieldset>

    <!-- Breast (Left) -->
    <fieldset>
      <legend>Breast (Left)</legend>
      <label><input type="checkbox" name="breast_left[]" value="mass"> Mass</label>
      <label><input type="checkbox" name="breast_left[]" value="nipple"> Nipple Discharge</label>
      <label><input type="checkbox" name="breast_left[]" value="skin"> Skin Dimpling</label>
      <label><input type="checkbox" name="breast_left[]" value="axillary"> Enlarged Axillary Nodes</label>
    </fieldset>

    <!-- Breast (Right) -->
    <fieldset>
      <legend>Breast (Right)</legend>
      <label><input type="checkbox" name="breast_right[]" value="mass"> Mass</label>
      <label><input type="checkbox" name="breast_right[]" value="nipple"> Nipple Discharge</label>
      <label><input type="checkbox" name="breast_right[]" value="skin"> Skin Dimpling</label>
      <label><input type="checkbox" name="breast_right[]" value="axillary"> Enlarged Axillary Nodes</label>
    </fieldset>

    <!-- Thorax -->
    <fieldset>
      <legend>Thorax</legend>
      <label><input type="checkbox" name="thorax[]" value="heart"> Abnormal Heart Sounds</label>
      <label><input type="checkbox" name="thorax[]" value="breath"> Abnormal Breath Sounds</label>
    </fieldset>

    <!-- Abdomen -->
    <fieldset>
      <legend>Abdomen</legend>
      <label><input type="checkbox" name="abdomen[]" value="liver"> Enlarged Liver</label>
      <label><input type="checkbox" name="abdomen[]" value="mass"> Mass</label>
      <label><input type="checkbox" name="abdomen[]" value="tenderness"> Tenderness</label>
    </fieldset>

    <!-- Extremities -->
    <fieldset>
      <legend>Extremities</legend>
      <label><input type="checkbox" name="extremities[]" value="edema"> Edema</label>
      <label><input type="checkbox" name="extremities[]" value="varicosities"> Varicosities</label>
    </fieldset>

    <!-- Cervix -->
    <fieldset>
      <legend>Cervix</legend>
      <label>Consistency:
        <select name="cervix_consistency">
          <option value="">--Select--</option>
          <option value="Soft" selected>Soft</option>
          <option value="Firm">Firm</option>
        </select>
      </label>
    </fieldset>

    <!-- Uterus -->
    <fieldset>
      <legend>Uterus</legend>
      <label>Position:
        <select name="uterus_position">
          <option value="">--Select--</option>
          <option value="Mid" selected>Mid</option>
          <option value="Anteflexed">Anteflexed</option>
          <option value="Retroflexed">Retroflexed</option>
        </select>
      </label>
      <label>Size:
        <select name="uterus_size">
          <option value="">--Select--</option>
          <option value="Small">Small</option>
          <option value="Normal" selected>Normal</option>
          <option value="Large">Large</option>
        </select>
      </label>
    </fieldset>

    <!-- Adnexa -->
    <fieldset>
      <legend>Adnexa</legend>
      <label><input type="checkbox" name="adnexa[]" value="mass"> Mass</label>
      <label><input type="checkbox" name="adnexa[]" value="tenderness"> Tenderness</label>
    </fieldset>

    <br>
    <button type="submit">Submit Test Record</button>
  </form>
</body>
</html>