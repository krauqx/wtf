<?php
session_start();
include_once __DIR__ . '/../config/url.php';

// Simulate a logged-in patient (user_id = 3 maps to patient_id = 1)
$_SESSION['user_id'] = 3;
$_SESSION['role'] = 'patient';
$_SESSION['user_role'] = 'patient';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AJAX Test: Physical Exam</title>
</head>
<body>
  <h2>Patient Physical Exam Record</h2>
  <pre id="output">Loading...</pre>

  <script>
    fetch("<?= $getPhysicalExamURL ?>", {
      credentials: 'same-origin' // Ensures cookies/session are sent
    })
    .then(response => {
      if (!response.ok) {
        throw new Error("HTTP " + response.status);
      }
      return response.json();
    })
    .then(data => {
      document.getElementById("output").textContent = JSON.stringify(data, null, 2);
    })
    .catch(error => {
      document.getElementById("output").textContent = "Error: " + error.message;
    });
  </script>
</body>
</html>