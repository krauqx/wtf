<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Test Appointment Status Update</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
      background: #f9f9f9;
    }
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
    }
    input, select, button {
      margin-bottom: 20px;
      padding: 8px;
      width: 300px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    button {
      background-color: #6c336e;
      color: white;
      cursor: pointer;
    }
  </style>
</head>
<body>

  <h2>Test Appointment Status Update</h2>

  <label for="appointment_id">Appointment ID:</label>
  <input type="number" id="appointment_id" placeholder="Enter appointment ID">

  <label for="status">New Status:</label>
  <select id="status">
    <option value="Waiting">Waiting</option>
    <option value="Ongoing">Ongoing</option>
    <option value="Done">Done</option>
  </select>

  <button onclick="submitStatusUpdate()">Submit Update</button>

  <div id="result" style="margin-top: 20px; font-weight: bold;"></div>

  <script>
    function submitStatusUpdate() {
      const appointmentId = document.getElementById('appointment_id').value;
      const status = document.getElementById('status').value;
      const resultDiv = document.getElementById('result');

      fetch('http://localhost/JAM_Lyingin/auth/action/staff/staff_update_appointment_status.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          appointment_id: appointmentId,
          status: status
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          resultDiv.textContent = `✅ Status updated to "${status}" for appointment ID ${appointmentId}`;
          resultDiv.style.color = 'green';
        } else {
          resultDiv.textContent = `❌ Failed: ${data.message}`;
          resultDiv.style.color = 'red';
        }
      })
      .catch(err => {
        resultDiv.textContent = `❌ Error: ${err.message}`;
        resultDiv.style.color = 'red';
      });
    }
  </script>

</body>
</html>