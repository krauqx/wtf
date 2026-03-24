<?php
session_start();
require_once(__DIR__ . '/../../../config/config.php');

$patientId = $_SESSION['selectedPatientId'] ?? 0;

if (!$patientId) {
    echo "<p style='color:red;'>No patient selected.</p>";
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        pr.*, 
        u.email, u.contact AS user_contact, u.role, u.date_created AS user_created, u.date_updated AS user_updated
    FROM patient_records pr
    JOIN users u ON pr.user_id = u.id
    WHERE pr.patient_id = ?
    LIMIT 1
");
$stmt->execute([$patientId]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    echo "<p style='color:red;'>Patient record not found.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Profile</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f6fa;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 1500px;
      margin: 40px auto;
      background: transparent;
      border-radius: 0;
      box-shadow: none;
      padding: 32px;
      position: relative;
    }
    h2 {
      margin-top: 0;
      color: #2c3e50;
      border-bottom: 2px solid #C97DBE;
      padding-bottom: 10px;
    }
    .profile-header {
      display: flex;
      align-items: center;
      margin-bottom: 30px;
    }
    .profile-header img {
      width: 120px;
      height: 120px;
      border-radius: 8px;
      object-fit: cover;
      margin-right: 20px;
      border: 2px solid #C97DBE;
    }
    .profile-header .name {
      font-size: 24px;
      font-weight: bold;
      color: #34495e;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      text-align: left;
      padding: 10px;
      border-bottom: 1px solid #ecf0f1;
    }
    th {
      background-color: #ecf0f1;
      color: #2c3e50;
      width: 220px;
    }
    .section-title {
      margin-top: 40px;
      font-size: 20px;
      color: #C97DBE;
      border-bottom: 1px solid #ccc;
      padding-bottom: 5px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Patient Profile</h2>

    <div style="position: absolute; top: 16px; right: 32px; z-index: 10;">
      <button onclick="window.location.href='/JAM_Lyingin/clerkdash.php#patients'" style="padding: 10px 20px; background-color: #C97DBE; color: white; border: none; border-radius: 5px; cursor: pointer;">Back
      </button>
    </div>

    <div class="profile-header">
      <?php if (!empty($patient['patient_image'])): ?>
        <img src="/uploads/patients/<?= htmlspecialchars($patient['patient_image']) ?>" alt="Patient Image">
      <?php else: ?>
        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEyMCIgaGVpZ2h0PSIxMjAiIGZpbGw9IiNmOGY5ZmUiLz48Y2lyY2xlIGN4PSI2MCIgY3k9IjQ2IiByPSIzMiIgZmlsbD0iI2ZlZWNmZiIvPjxyZWN0IHg9IjM2IiB5PSI4MiIgd2lkdGg9IjQ4IiBoZWlnaHQ9IjI4IiByeD0iMTQiIGZpbGw9IiNlMmU4ZjAiLz48L3N2Zz4=" alt="Default Avatar">
      <?php endif; ?>
      <div class="name"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['middle_name'] . ' ' . $patient['last_name']) ?></div>
    </div>

    <div class="section-title">Personal Information</div>
    <table>
      <tr><th>Date of Birth</th><td><?= htmlspecialchars($patient['date_of_birth']) ?></td></tr>
      <tr><th>Age</th><td><?= htmlspecialchars($patient['age']) ?></td></tr>
      <tr><th>Gender</th><td><?= htmlspecialchars($patient['gender']) ?></td></tr>
      <tr><th>Civil Status</th><td><?= htmlspecialchars($patient['status']) ?></td></tr>
      <tr><th>Contact Number</th><td><?= htmlspecialchars($patient['contact_number']) ?></td></tr>
      <tr><th>Occupation</th><td><?= htmlspecialchars($patient['occupation']) ?></td></tr>
      <tr><th>Address</th><td><?= htmlspecialchars($patient['address']) ?></td></tr>
    </table>

    <div class="section-title">Emergency Contact</div>
    <table>
      <tr><th>Contact Person</th><td><?= htmlspecialchars($patient['emergency_name']) ?></td></tr>
      <tr><th>Relationship</th><td><?= htmlspecialchars($patient['relationship']) ?></td></tr>
      <tr><th>Contact Number</th><td><?= htmlspecialchars($patient['emergency_contact']) ?></td></tr>
      <tr><th>Address</th><td><?= htmlspecialchars($patient['emergency_address']) ?></td></tr>
    </table>

    <div class="section-title">Account Information</div>
    <table>
      <tr><th>Email</th><td><?= htmlspecialchars($patient['email']) ?></td></tr>
      <tr><th>Role</th><td><?= htmlspecialchars($patient['role']) ?></td></tr>
      <tr><th>Account Created</th><td><?= htmlspecialchars($patient['user_created']) ?></td></tr>
      <tr><th>Last Updated</th><td><?= htmlspecialchars($patient['user_updated']) ?></td></tr>
    </table>
  </div>
</body>
</html>
