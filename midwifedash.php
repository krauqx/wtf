<?php

session_start();
include_once 'config/config.php';
include_once 'config/roleGate.php';
requireRole(['staff', 'admin']);
if (isset($_SESSION['user_id'])) {
    $userId = (int) $_SESSION['user_id'];

    // Only set once if not already set
    if (!isset($_SESSION['doctor_name'])) {
        try {
            $stmt = $pdo->prepare("SELECT last_name FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $row = $stmt->fetch();

            if ($row && !empty($row['last_name'])) {
                $_SESSION['doctor_name'] = $row['last_name'];
            } else {
                $_SESSION['doctor_name'] = 'Unknown'; // fallback if no record
            }
        } catch (PDOException $e) {
            // Handle DB error gracefully
            $_SESSION['doctor_name'] = 'Error';
        }
    }
} else {
    // No logged-in user
    $_SESSION['doctor_name'] = 'Guest';
}



// Get current date info
$current_month = date('F Y');
$current_day = date('j');
$days_in_month = date('t');
$first_day_of_month = date('w', strtotime(date('Y-m-01')));

// Sample data
$appointments_today = 0;
$available_slots = 8;
$high_risk_cases = 3;

// Generate calendar days
function generateCalendar($year, $month) {
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $first_day = date('w', mktime(0, 0, 0, $month, 1, $year));
    $current_day = date('j');
    $current_month = date('n');
    $current_year = date('Y');
    
    $calendar = [];
    
    // Add empty cells for days before the first day of the month
    for ($i = 0; $i < $first_day; $i++) {
        $calendar[] = '';
    }
    
    // Add days of the month
    for ($day = 1; $day <= $days_in_month; $day++) {
        $is_today = ($day == $current_day && $month == $current_month && $year == $current_year);
        $calendar[] = ['day' => $day, 'is_today' => $is_today];
    }
    
    return $calendar;
}

$calendar_data = generateCalendar(date('Y'), date('n'));

// AJAX endpoint for calendar update
if (isset($_GET['calendar_ajax']) && isset($_GET['year']) && isset($_GET['month'])) {
    $year = intval($_GET['year']);
    $month = intval($_GET['month']);
    $calendar_data = generateCalendar($year, $month);
    ob_start();
    ?>
    <div class="calendar-grid">
        <div class="calendar-day-header">Sun</div>
        <div class="calendar-day-header">Mon</div>
        <div class="calendar-day-header">Tue</div>
        <div class="calendar-day-header">Wed</div>
        <div class="calendar-day-header">Thu</div>
        <div class="calendar-day-header">Fri</div>
        <div class="calendar-day-header">Sat</div>
    </div>
    <div class="calendar-grid">
        <?php foreach ($calendar_data as $day): ?>
            <?php if (empty($day)): ?>
                <div class="calendar-day empty"></div>
            <?php else: ?>
                <div class="calendar-day <?php echo $day['is_today'] ? 'today' : ''; ?>">
                    <?php echo $day['day']; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php
    echo ob_get_clean();
    exit;
}

// AJAX endpoint for calendar update
if (isset($_GET['calendar_ajax']) && isset($_GET['year']) && isset($_GET['month'])) {
    $year = intval($_GET['year']);
    $month = intval($_GET['month']);
    $calendar_data = generateCalendar($year, $month);
    ob_start();
    ?>
    <div class="calendar-grid">
        <div class="calendar-day-header">Sun</div>
        <div class="calendar-day-header">Mon</div>
        <div class="calendar-day-header">Tue</div>
        <div class="calendar-day-header">Wed</div>
        <div class="calendar-day-header">Thu</div>
        <div class="calendar-day-header">Fri</div>
        <div class="calendar-day-header">Sat</div>
    </div>
    <div class="calendar-grid">
        <?php foreach ($calendar_data as $day): ?>
            <?php if (empty($day)): ?>
                <div class="calendar-day empty"></div>
            <?php else: ?>
                <div class="calendar-day <?php echo $day['is_today'] ? 'today' : ''; ?>">
                    <?php echo $day['day']; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php
    echo ob_get_clean();
    exit;
}

//Fetch patient info if a patient is selected
class PatientRecord {
    public static $id;
    public static $firstName;
    public static $middleName;
    public static $lastName;
    public static $dob;
    public static $age;
    public static $gender;
    public static $status;
    public static $contact;
    public static $occupation;
    public static $address;
    public static $image;
    public static $createdAt;
    public static $updatedAt;
    public static $emergencyName;
    public static $emergencyContact;
    public static $emergencyAddress;
    public static $relationship;
    public static function loadById($pdo, $patientId) {
        $stmt = $pdo->prepare("SELECT * FROM patient_records WHERE patient_id = ?");
        $stmt->execute([$patientId]);
        $record = $stmt->fetch();

        if ($record) {
            self::$id         = $record['patient_id'];
            self::$firstName  = $record['first_name'];
            self::$middleName = $record['middle_name'];
            self::$lastName   = $record['last_name'];
            self::$dob        = $record['date_of_birth'];
            self::$age        = $record['age'];
            self::$gender     = $record['gender'];
            self::$status     = $record['status'];
            self::$contact    = $record['contact_number'];
            self::$occupation = $record['occupation'];
            self::$address    = $record['address'];
            self::$image      = $record['patient_image'];
            self::$createdAt  = $record['created_at'];
            self::$updatedAt  = $record['updated_at'];
            self::$emergencyName    = $record['emergency_name'];
            self::$emergencyContact = $record['emergency_contact'];
            self::$emergencyAddress = $record['emergency_address'];
            self::$relationship     = $record['relationship'];

            return true;
        }
        return false;
    }
}
// ✅ Handle both JSON and form POSTs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isJson = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    $input = $isJson
        ? json_decode(file_get_contents('php://input'), true)
        : $_POST;

    // 🔧 Set currentPage if provided
    if (isset($input['currentPage'])) {
        $_SESSION['currentPage'] = $input['currentPage'];
        if ($input['currentPage'] === 'Medical Records') {
            $_SESSION['flashShowMedical'] = true;
        }
    }

    // 🔧 Set selectedPatientId from either source
    if (isset($input['selectedPatientId'])) {
        $patientId = (int) $input['selectedPatientId'];
        $_SESSION['selectedPatientId'] = $patientId;
    } elseif (isset($input['patient_id'])) {
        $patientId = (int) $input['patient_id'];
        $_SESSION['selectedPatientId'] = $patientId;
    }

    // ✅ Respond if JSON (but do NOT exit yet)
if ($isJson) {
    echo json_encode(['status' => 'success']);
    exit; // ✅ This is critical
}

}

// ✅ Load patient record if session ID is set
if (isset($_SESSION['selectedPatientId'])) {
    $patientId = $_SESSION['selectedPatientId'];
    if (!PatientRecord::loadById($pdo, $patientId)) {
        if (!str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            die("No record found for patient ID: $patientId");
        }
    }
}

// ✅ Default page fallback: default to Home on non-POST loads unless viewing Medical Records
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!empty($_SESSION['flashShowMedical'])) {
        $_SESSION['currentPage'] = 'Medical Records';
        unset($_SESSION['flashShowMedical']);
    } else {
        $_SESSION['currentPage'] = 'Home';
    }
}

// ✅ Load patient list
$patients = [];
try {
    $stmt = $pdo->query("SELECT patient_id, first_name, middle_name, last_name, age, gender, status FROM patient_records ORDER BY last_name ASC");
    $patients = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<p>Error loading patient list: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// ✅ Debug log
file_put_contents(__DIR__ . '/debug.txt', "RAW POST ID ITO: " . file_get_contents('php://input') . "\n", FILE_APPEND);

//Loading Analytics

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JAM Lying-in Clinic - Dashboard</title>
   
    <link rel="stylesheet" href="midwifedash.css">
</head>
<body>
   <script>console.log("Fetched literally ID: <?= $_SESSION['currentPage']; ?>");</script>
 
    <div class="sidebar">
        <div class="logo">JAM Lying-in Clinic</div>
        
        <div class="doctor-info">
            <div class="doctor-avatar">👨‍⚕️</div>
            <div class="doctor-name"><?php echo $_SESSION['doctor_name']; ?></div>
        </div>

        <nav class="nav-menu">
            <a href="#" class="nav-item active">
                <span class="nav-icon">🏠</span>
                Home
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">👥</span>
                Patients
            </a>


        </nav>

        <div style="margin-top: auto;">
            <a href="#" class="nav-item">
                <span class="nav-icon">⚙️</span>
                Settings
            </a>
            <button class="sign-out" onclick="signOut()">
                <span>🚪</span>
                Sign Out
            </button>
        </div>
    </div>

    <div class="main-content">
        <div class="content-section">
            <h2 style="margin-bottom: 28px; color: #222;">Welcome <?= $_SESSION['doctor_name']?></h2>
            <div class="dashboard-grid">
                <!-- Approved Appointments Section -->
                <div id="approved-appointments-section" class="appointments-section" style="margin-top: 0; background: rgba(255,255,255,0.95); border-radius: 15px; padding: 25px; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
                  <h3 style="color: #6c336e; margin-bottom: 18px;">Approved Appointments</h3>

                  <!-- Loader -->
                  <div id="appointments-loader" style="text-align: center; padding: 40px; color: #6b7280;">
                    <span style="font-size: 24px;">⏳ Loading appointments...</span>
                  </div>

                  <!-- Table Container -->
                  <div id="appointments-table-wrapper" style="display: none;">
                    <table style="width: 100%; border-collapse: collapse;">
                      <thead>
                        <tr style="background: #f3e6f7; color: #6c336e;">
                          <th style="padding: 10px 8px; text-align: left; border-radius: 8px 0 0 8px;">Patient ID</th>
                          <th style="padding: 10px 8px; text-align: left;">Patient Name</th>
                          <th style="padding: 10px 8px; text-align: left;">Date & Time</th>
                          <th style="padding: 10px 8px; text-align: left;">Concern</th>
                          <th style="padding: 10px 8px; text-align: left; border-radius: 0 8px 8px 0;">Status</th>
                        </tr>
                      </thead>
                      <tbody id="appointments-table-body">
                        <!-- Rows will be injected here -->
                      </tbody>
                    </table>
                  </div>

                  <!-- Error Message -->
                  <div id="appointments-error" style="display: none; margin-top: 20px; color: green; font-size: 16px; text-align: center;">
                    No approved appointments.
                  </div>
                </div>
                
<script>
function fetchApprovedAppointments() {
  const loader = document.getElementById('appointments-loader');
  const tableWrapper = document.getElementById('appointments-table-wrapper');
  const tableBody = document.getElementById('appointments-table-body');
  const errorBlock = document.getElementById('appointments-error');

  loader.style.display = 'block';
  tableWrapper.style.display = 'none';
  errorBlock.style.display = 'none';
  tableBody.innerHTML = '';

  fetch('http://localhost/JAM_LYINGIN/auth/action/staff/staff_get_approved_appointments.php')
    .then(res => res.json())
    .then(data => {
      loader.style.display = 'none';

      if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
        tableWrapper.style.display = 'block';

        data.data.forEach(appt => {
          const row = document.createElement('tr');
          row.setAttribute('data-id', appt.id);
          row.style.borderBottom = '1px solid #eee';

          // Patient ID
          const cellPatientId = document.createElement('td');
          cellPatientId.style.padding = '10px 8px';
          cellPatientId.textContent = appt.patient_id;

          // Patient Name
          const cellPatientName = document.createElement('td');
          cellPatientName.style.padding = '10px 8px';
          cellPatientName.textContent = appt.patient_name || '—';


          // Date & Time
          const cellDateTime = document.createElement('td');
          cellDateTime.style.padding = '10px 8px';
          cellDateTime.textContent = `${appt.appointment_date} ${appt.appointment_time}`;

          // Concern
          const cellConcern = document.createElement('td');
          cellConcern.style.padding = '10px 8px';
          cellConcern.textContent = appt.chief_complaint;

          // Status Dropdown
          const cellStatus = document.createElement('td');
          cellStatus.style.padding = '10px 8px';

          const statusSelect = document.createElement('select');
          statusSelect.name = 'status[]';
          statusSelect.style.padding = '4px 8px';
          statusSelect.style.borderRadius = '6px';
          statusSelect.style.border = '1px solid #ccc';

          const allowedStatuses = ['Approved','Waiting', 'Ongoing', 'Done', 'Cancelled'];
          const normalizedStatus = appt.status?.trim() || '';

          const uniqueStatuses = new Set([
            ...allowedStatuses.map(s => s.toLowerCase()),
            normalizedStatus.toLowerCase()
          ]);

          uniqueStatuses.forEach(status => {
            const option = document.createElement('option');
            option.value = status.charAt(0).toUpperCase() + status.slice(1);
            option.textContent = option.value;
            if (normalizedStatus.toLowerCase() === status) option.selected = true;
            statusSelect.appendChild(option);
          });


          statusSelect.addEventListener('change', () => {
            const newStatus = statusSelect.value;
            const appointmentId = row.getAttribute('data-id');
            updateAppointmentStatus(appointmentId, newStatus);
          });

          cellStatus.appendChild(statusSelect);

          // Append all cells to row
          row.appendChild(cellPatientId);
          row.appendChild(cellPatientName);
          row.appendChild(cellDateTime);
          row.appendChild(cellConcern);
          row.appendChild(cellStatus);

          tableBody.appendChild(row);
        });

        console.log(`✅ Loaded ${data.data.length} approved appointments.`);
      } else {
        errorBlock.style.display = 'block';
        console.warn('⚠️ No appointments found or error:', data.message);
      }
    })
    .catch(err => {
      loader.style.display = 'none';
      errorBlock.style.display = 'block';
      console.error('❌ Fetch error:', err);
    });
}

function updateAppointmentStatus(appointmentId, newStatus) {
  console.log("ASFFJAJASF YOU REACHED ME")
  fetch('http://localhost/JAM_LYINGIN/auth/action/staff/staff_update_appointment_status.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      appointment_id: appointmentId,
      status: newStatus
    })
  })
  .then(res => res.json())
  .then(result => {
    if (result.success) {
      console.log(`✅ Status updated for appointment ${appointmentId} → ${newStatus}`);
    } else {
      console.warn(`⚠️ Failed to update status:`, result.message);
    }
  })
  .catch(err => {
    console.error('❌ Update error:', err);
  });
}

document.addEventListener('DOMContentLoaded', fetchApprovedAppointments);
</script>
                
                             <div class="calendar-section">
                    <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 8px;">
                        <div id="current-time" style="font-size: 1.2rem; color: #fff; background: linear-gradient(90deg, #764ba2 0%, #ff6bcb 100%); padding: 8px 22px; border-radius: 10px; font-weight: 500; box-shadow: 0 2px 8px rgba(118,75,162,0.08);"></div>
                    </div>
                    <div class="calendar-header">
                        <div class="calendar-title" id="calendar-title"><?php echo $current_month; ?></div>
                        <div class="calendar-nav">
                            <button class="nav-btn" id="calendar-prev">←</button>
                            <button class="nav-btn" id="calendar-next">→</button>
                        </div>
                    </div>

                    <div class="calendar" id="calendar-container">
                        <div class="calendar-grid">
                            <div class="calendar-day-header">Sun</div>
                            <div class="calendar-day-header">Mon</div>
                            <div class="calendar-day-header">Tue</div>
                            <div class="calendar-day-header">Wed</div>
                            <div class="calendar-day-header">Thu</div>
                            <div class="calendar-day-header">Fri</div>
                            <div class="calendar-day-header">Sat</div>
                        </div>
                        
                        <div class="calendar-grid">
                            <?php foreach ($calendar_data as $day): ?>
                                <?php if (empty($day)): ?>
                                    <div class="calendar-day empty"></div>
                                <?php else: ?>
                                    <div class="calendar-day <?php echo $day['is_today'] ? 'today' : ''; ?>">
                                        <?php echo $day['day']; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                    <script>
                    
                    </script>


        <!-- Patient List Dashboard Section (hidden by default) -->
      <div id="patient-list-section" class="content-section" style="margin-top: 30px; display: none;">
          <h2 style="color: #6c336e; font-size: 2rem; font-weight: bold; margin-bottom: 24px; letter-spacing: 1px;">PATIENT LIST</h2>
          <input id="patient-search" type="text" placeholder="Search by Patient ID or Name" style="width: 100%; padding: 14px 18px; border-radius: 12px; border: 1px solid #eee; margin-bottom: 22px; font-size: 1.1rem; outline: none; box-shadow: 0 2px 8px rgba(108,51,110,0.04);">
          <div style="overflow-x: auto;">
              <table id="patient-table" style="width: 100%; border-collapse: separate; border-spacing: 0; min-width: 700px; background: white; border-radius: 18px; overflow: hidden; box-shadow: 0 4px 16px rgba(108,51,110,0.07);">
                  <thead>
                      <tr style="background: linear-gradient(90deg, rgb(251, 137, 184) 0%, #764ba2 100%); color: #fff;">
                          <th style="padding: 16px 18px; text-align: left; font-size: 1.08rem; font-weight: bold; border-radius: 18px 0 0 0;">Patient ID</th>
                          <th style="padding: 16px 18px; text-align: left; font-size: 1.08rem; font-weight: bold;">Name</th>
                          <th style="padding: 16px 18px; text-align: left; font-size: 1.08rem; font-weight: bold;">Age</th>
                          <th style="padding: 16px 18px; text-align: left; font-size: 1.08rem; font-weight: bold;">Gender</th>
                          <th style="padding: 16px 18px; text-align: left; font-size: 1.08rem; font-weight: bold;">Status</th>
                          <th style="padding: 16px 18px; text-align: left; font-size: 1.08rem; font-weight: bold; border-radius: 0 18px 0 0;">Actions</th>
                      </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($patients as $patient): ?>
                      <tr>
                          <td style="padding: 14px 18px;"><?= htmlspecialchars($patient['patient_id']) ?></td>
                          <td style="padding: 14px 18px;">
                              <?= htmlspecialchars($patient['last_name'] . ', ' . $patient['first_name'] . ' ' . $patient['middle_name']) ?>
                          </td>
                          <td style="padding: 14px 18px;"><?= htmlspecialchars($patient['age']) ?></td>
                          <td style="padding: 14px 18px;"><?= htmlspecialchars($patient['gender']) ?></td>
                          <td style="padding: 14px 18px;"><?= htmlspecialchars($patient['status']) ?></td>
                            <td style="padding: 14px 18px;">
                              <button
                                class="view-record-btn"
                                data-patient-id="<?= $patient['patient_id'] ?>"
                                style="padding: 6px 12px; border-radius: 6px; background-color: #6c336e; color: white; border: none;">
                                View
                              </button>
                            </td>

                      </tr>
                  <?php endforeach; ?>
                  </tbody>

              </table>
          </div>
      </div>
<script>
document.querySelectorAll('.view-record-btn').forEach(button => {
  button.addEventListener('click', function () {
    const patientId = this.getAttribute('data-patient-id');
    console.log('Sending patient ID:', patientId);

    // Send session update to PHP
    fetch('dashboard.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({
        currentPage: 'Medical Records',
        selectedPatientId: patientId
      })
    })
    .then(res => res.json())
    .then(data => {
      console.log(`[Session updated] ID: ${patientId}`, data);
      showMedicalRecordsSection();
      window.location.reload();
    })
    .catch(err => {
      console.error('Session update failed:', err);
    });
  });
});


function showMedicalRecordsSection() {
  document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
  const medTab = Array.from(document.querySelectorAll('.nav-item')).find(nav =>
    nav.textContent.trim().includes('Medical Records')
  );
  if (medTab) medTab.classList.add('active');

  const mainContent = document.querySelector('.main-content > .content-section');
  const patientList = document.getElementById('patient-list-section');
  const medRecords = document.getElementById('medical-records-section');

  if (mainContent) mainContent.style.display = 'none';
  if (patientList) patientList.style.display = 'none';
  if (medRecords) medRecords.style.display = '';
}
document.addEventListener('DOMContentLoaded', function () {
  const currentPage = "<?= $_SESSION['currentPage'] ?? '' ?>";
  if (currentPage === "Medical Records") {
    showMedicalRecordsSection();
  }
});

</script>      
<!-- Settings Section (hidden by default) -->
        <div id="settings-section" class="content-section" style="margin-top: 30px; display: none;">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">
                        <span>⚙️</span>
                    </div>
                    <div>
                        <h2 class="settings-card-title">Settings</h2>
                        <p class="settings-card-subtitle">Manage your account preferences and security settings</p>
                    </div>
                </div>

                <div class="settings-form">
                    <!-- Profile Settings
                    <div class="settings-form-group">
                        <h3 style="color: #2d3748; font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Profile Information</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label class="settings-label">Full Name *</label>
                                <input type="text" id="doctorName" class="settings-input" placeholder="Enter your full name" value="Dr. Gregorio" style="width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px; font-size:16px; background:#fff; box-sizing:border-box;">
                            </div>
                            <div>
                                <label class="settings-label">Email Address</label>
                                <input type="email" id="doctorEmail" class="settings-input" placeholder="Enter your email" style="width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px; font-size:16px; background:#fff; box-sizing:border-box;">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label class="settings-label">Phone Number</label>
                                <input type="tel" id="doctorPhone" class="settings-input" placeholder="Enter your phone number" style="width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px; font-size:16px; background:#fff; box-sizing:border-box;">
                            </div>
                            <div>
                                <label class="settings-label">Medical License</label>
                                <input type="text" id="doctorLicense" class="settings-input" placeholder="Enter your license number" style="width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px; font-size:16px; background:#fff; box-sizing:border-box;">
                            </div>
                        </div>
                        
                        <button type="button" class="settings-button" onclick="saveProfileSettings()" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:#fff; border:none; padding:12px 24px; border-radius:12px; font-size:16px; font-weight:600; box-shadow:0 4px 12px rgba(102,126,234,0.3);">Save Profile</button>
                    </div> -->

                    <!-- Security Settings -->
                    <div class="settings-form-group">
                        <h3 style="color: #2d3748; font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Security Settings</h3>
                        
                        <div style="margin-bottom: 20px;">
                            <label class="settings-label">Current Password *</label>
                            <input type="password" id="currentPassword" class="settings-input" placeholder="Enter your current password" style="width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px; font-size:16px; background:#fff; box-sizing:border-box;">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label class="settings-label">New Password *</label>
                                <input type="password" id="newPassword" class="settings-input" placeholder="Enter new password" style="width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px; font-size:16px; background:#fff; box-sizing:border-box;">
                            </div>
                            <div>
                                <label class="settings-label">Confirm New Password *</label>
                                <input type="password" id="confirmPassword" class="settings-input" placeholder="Confirm new password" style="width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px; font-size:16px; background:#fff; box-sizing:border-box;">
                            </div>
                        </div>
                        
                        <button type="button" class="settings-button" onclick="changePassword()" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:#fff; border:none; padding:12px 24px; border-radius:12px; font-size:16px; font-weight:600; box-shadow:0 4px 12px rgba(102,126,234,0.3);">Change Password</button>
                        <script>
                        function changePassword() {
                          const currentPassword = document.getElementById('currentPassword').value.trim();
                          const newPassword = document.getElementById('newPassword').value.trim();
                          const confirmPassword = document.getElementById('confirmPassword').value.trim();

                          if (!currentPassword || !newPassword || !confirmPassword) {
                            alert('Please fill out all fields.');
                            return;
                          }

                          if (newPassword !== confirmPassword) {
                            alert('New password and confirmation do not match.');
                            return;
                          }

                          fetch('auth/action/staff/staff_change_password.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                              current_password: currentPassword,
                              new_password: newPassword
                            })
                          })
                          .then(res => res.json())
                          .then(data => {
                            if (data.status === 'success') {
                              alert('Password changed successfully.');
                              document.getElementById('currentPassword').value = '';
                              document.getElementById('newPassword').value = '';
                              document.getElementById('confirmPassword').value = '';
                            } else {
                              alert(data.message || 'Password change failed.');
                            }
                          })
                          .catch(err => {
                            console.error('Password change error:', err);
                            alert('An error occurred while changing password.');
                          });
                        }
                        </script>
                      </div>

                    <!-- System Preferences
                    <div class="settings-form-group">
                        <h3 style="color: #2d3748; font-size: 18px; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">System Preferences</h3>
                        
                        <div class="settings-toggle-group">
                            <div class="settings-toggle-info">
                                <div class="settings-toggle-title">Email Notifications</div>
                                <div class="settings-toggle-description">Receive email notifications for appointments and updates</div>
                            </div>
                            <label class="settings-toggle">
                                <input type="checkbox" id="emailNotifications">
                                <span class="settings-toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="settings-toggle-group">
                            <div class="settings-toggle-info">
                                <div class="settings-toggle-title">Auto Save</div>
                                <div class="settings-toggle-description">Automatically save changes as you work</div>
                            </div>
                            <label class="settings-toggle">
                                <input type="checkbox" id="autoSave">
                                <span class="settings-toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="settings-toggle-group">
                            <div class="settings-toggle-info">
                                <div class="settings-toggle-title">Dark Mode</div>
                                <div class="settings-toggle-description">Switch to dark theme for better viewing</div>
                            </div>
                            <label class="settings-toggle">
                                <input type="checkbox" id="darkMode">
                                <span class="settings-toggle-slider"></span>
                            </label>
                        </div>
                        
                        <button type="button" class="settings-button" onclick="saveSystemPreferences()">Save Preferences</button>
                    </div> -->
                </div>
            </div>
        </div>   
        <!-- Medical Records Dashboard Section (hidden by default) -->
        <div id="medical-records-section" class="content-section" style="margin-top: 30px;  background: #f9fafc; border-radius: 20px; display: none;">
            <h2 style="margin-bottom: 10px; color: #222;">Patient Medical Records</h2>
            <div style="max-width: 1200px; margin: 0 auto; padding: 32px; display: grid; grid-template-columns: 1fr 2fr; gap: 32px; align-items: flex-start;">
                <!-- Patient Info Left -->
                 
                <div style="min-width: 320px; max-width: 420px; display: flex; flex-direction: column; gap: 24px;">
                    <!-- Patient Information Card -->
                    <div style="height: 555px; background: white; border-radius: 20px; padding: 32px; box-shadow: 0 4px 24px rgba(108, 51, 110, 0.06); display: flex; flex-direction: column; gap: 32px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 32px;">
                                <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background:rgb(255, 255, 255);">
                                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSI0MCIgY3k9IjQwIiByPSI0MCIgZmlsbD0iI0Y3RkFGQyIvPjxjaXJjbGUgY3g9IjQwIiBjeT0iMzAiIHI9IjEyIiBmaWxsPSIjNEE1NTY4Ii8+PHBhdGggZD0iTTIwIDYwYzAtMTEgOS0yMCAyMC0yMHMyMCA5IDIwIDIwdjEwSDIwVjYweiIgZmlsbD0iIzRBNTU2OCIvPjwvc3ZnPg==" alt="Patient Photo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #e2e8f0;">
                                </div>
                                <div>
                                    <h2 style="font-size: 24px; font-weight: 600; color: #2d3748; margin-bottom: 4px;"> <?= htmlspecialchars(PatientRecord::$lastName) ?>, <?= htmlspecialchars(PatientRecord::$firstName) ?> </h2>
                                    <div style="color: #718096; font-size: 14px; margin-bottom: 4px;">
                                      <?= htmlspecialchars(!empty($patientId) ? $patientId : 'Select a patient first') ?>
                                    </div>

                                    <div style="color: #4299e1; font-size: 14px;"> <!-- emaiiill --> </div>
                                </div>
                            </div>
                          
                            <h3 style="color: #7c3a8a; font-size: 18px; font-weight: 700;">General Information</h3>
                            <!-- Awaiting backend data for patient info -->
                            <div class="info-item">
                              <span id="info-dob" class="info-value">
                                <p> </p>
                            </span>

                            </div>
                              <!--Put date of birth here-->
                          

                            <div class="info-item"><span class="info-label">Age:</span> 
                                <?= htmlspecialchars(PatientRecord::$age) ?> 
                            <span class="info-value"></span></div>

                            <div class="info-item"><span class="info-label">Gender:</span>
                              <span class="info-value">
                                <?= htmlspecialchars(PatientRecord::$gender) ?>
                             </span>
                            </div>
                            <div class="info-item"><span class="info-label">Status:</span>
                              <span class="info-value">
                                <?= htmlspecialchars(PatientRecord::$status) ?>
                              </span>
                            </div>
                            <div class="info-item"><span class="info-label">Contact Number:</span>
                             <span class="info-value">
                                <?= htmlspecialchars(PatientRecord::$contact) ?>
                             </span>
                            </div>
                            
                            <div class="info-item"><span class="info-label">Occupation:</span>
                            <span class="info-value">
                                <?= htmlspecialchars(PatientRecord::$occupation) ?> 
                            </span>
                          </div>
                            
                          <div class="info-item">
                              <span class="info-label">Address:</span>
                              <span class="info-value">
                                <?= htmlspecialchars(PatientRecord::$address) ?>
                              </span>
                          </div>
                            
                            <h3 style="color: #7c3a8a; font-size: 18px; font-weight: 700; margin-top: 20px;">In Case of Emergency</h3>
                            <!-- Awaiting backend data for emergency contact -->
                            
                          <div class="info-item"><span class="info-label">Name:</span>
                            <span class="info-value">
                                    <?= htmlspecialchars(PatientRecord::$gender) ?>
                            </span>
                          </div>
                            
                            <div class="info-item"><span class="info-label">Contact Number:</span>
                            <span class="info-value">
                                <?= htmlspecialchars(PatientRecord::$emergencyContact) ?>
                            </span>
                          </div>
                            
                          <div class="info-item">
                            <span class="info-label">Address:</span>
                            <span class="info-value">
                                <?= htmlspecialchars(PatientRecord::$emergencyAddress) ?> 
                            </span>
                          </div>
                        
                          </div>
                    </div>
                      <!--Baby book section-->
      
<div style="background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(44,62,80,0.08); padding: 0 12px; display: flex; align-items: center; gap: 18px; overflow-x: auto; white-space: nowrap; min-height: 100px;">
<h3 style="margin-bottom: 8px; color: #7c3a8a; font-size: 18px; font-weight: 500;">Baby Profiles</h3>                        
<div style="display: flex; align-items: center; gap: 18px; overflow-x: auto; white-space: nowrap; min-height: 120px;">
    <button type="button" id="createBabyImgBtn" style="min-width: 80px; min-height: 80px; width: 80px; height: 80px; border-radius: 50%; background: #fde6ef; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(44,62,80,0.08); margin-right: 8px;" onclick="document.getElementById('createBabyModal').style.display='block'">
        <span style="font-size: 40px; color: #6c336e; font-weight: bold;">+</span>
    </button>
    <a href="baby_profile.php?id=1" style="display: inline-block; min-width: 80px; min-height: 80px;">
        <img src="https://www.w3schools.com/w3images/fjords.jpg" alt="Baby Book 1" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%; box-shadow: 0 2px 8px rgba(44,62,80,0.08); cursor: pointer;">
    </a>
    <a href="baby_profile.php?id=2" style="display: inline-block; min-width: 80px; min-height: 80px;">
        <img src="https://www.w3schools.com/w3images/lights.jpg" alt="Baby Book 2" style="width: 80px; height: 80px; object-fit: cover; border-radius: 50%; box-shadow: 0 2px 8px rgba(44,62,80,0.08); cursor: pointer;">
    </a>
</div>
<div id="createBabyModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6);">
    <div style="background:#fff; border-radius:18px; padding:28px 24px; max-width:400px; width:90vw; box-sizing:border-box; position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); box-shadow:0 8px 32px rgba(44,62,80,0.18);">
        <span style="position:absolute;top:16px;right:20px;color:#764ba2;font-size:32px;font-weight:bold;cursor:pointer;" onclick="document.getElementById('createBabyModal').style.display='none'">&times;</span>
        <h3 style="color:#764ba2; font-size:22px; font-weight:700; margin-bottom:18px;">Create Baby Profile</h3>
        <form>
            <input type="text" placeholder="Baby Name" style="width:100%;margin-bottom:12px;padding:10px;border-radius:8px;border:1px solid #eee;font-size:16px;">
            <input type="date" style="width:100%;margin-bottom:12px;padding:10px;border-radius:8px;border:1px solid #eee;font-size:16px;">
            <input type="file" style="width:100%;margin-bottom:12px;">
            <button type="submit" style="width:100%;padding:10px 0;border-radius:8px;background:linear-gradient(90deg,#fb89b8 0%,#764ba2 100%);color:#fff;font-size:16px;font-weight:600;border:none;cursor:pointer;">Save</button>
        </form>
    </div>
</div>
                    </div>
                    <script>
                        function openBabyModal() {
                            var modal = document.getElementById('babyImgModal');
                            var img = document.getElementById('babyModalImg');
                            var modalImg = document.getElementById('img01');
                            var captionText = document.getElementById('caption');
                            modal.style.display = 'block';
                            modalImg.src = img.src;
                            captionText.innerHTML = img.alt;
                        }
                        function closeBabyModal() {
                            document.getElementById('babyImgModal').style.display = 'none';
                        }
                    </script>

                </div>
                <!-- Right Side: Analytics -->
                <div style="min-width: 340px; display: flex; flex-direction: column; gap: 24px;">
                    <div style="display: block; width: 100%;">
                        <div style="display: flex; flex-direction: column; gap: 24px; margin-bottom: 32px;">
                            <!-- Search Medical Records Card -->
                             

                            <div style="background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(44,62,80,0.08); padding: 28px 32px; display: flex; flex-direction: column; gap: 20px;">
                                <h3 style="color: #232b3b; font-size: 20px; font-weight: 700; margin: 0;">Search Medical Records</h3>
                                
                                <div>
                                    <!-- Date selector dropdown -->
                                    <select id="visitDateSelector" style="width: 100%; margin-bottom: 12px; padding: 12px 16px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff; transition: border 0.2s; box-sizing: border-box;">
                                      <option value="">Select a visit date</option>
                                    </select>

                                    <input type="date" id="searchVisitDate" value="" placeholder="00/00/00" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff; transition: border 0.2s; box-sizing: border-box;">
                                </div>
                                
                                <div style="display: flex; gap: 12px;">
                                    <button type="button" class="btn-primary" id="searchVisitDateBtn" style="flex: 1; padding: 12px 16px; font-size: 15px; font-weight: 600; border-radius: 8px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white; border: none; cursor: pointer; transition: all 0.2s ease;">Search Visit Date</button>
                                    <!-- <button type="button" class="btn-secondary" id="addVisitDateBtn" style="flex: 1; padding: 12px 16px; font-size: 15px; font-weight: 600; border-radius: 8px; background: linear-gradient(90deg, #f8f9fe 0%, #e0e7ff 100%); color: #7c3aed; border: none; cursor: pointer; transition: all 0.2s ease;">Add Visit Date</button> -->
                                </div>
                                

                            </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const dateInput = document.getElementById('searchVisitDate');
  const dateSelector = document.getElementById('visitDateSelector');

  // ✅ Set today's date as default
  const today = new Date().toISOString().split('T')[0];
  dateInput.value = today;

  // ✅ Fetch unified visit dates
  fetch('/JAM_LYINGin/auth/action/staff/staff_get_unified_visit_dates.php', {
    method: 'GET',
    credentials: 'same-origin'
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      const visitDates = data.dates;

      visitDates.forEach(rawDate => {
        const normalizedDate = rawDate.split(' ')[0]; // ✅ Strip time portion
        const option = document.createElement('option');
        option.value = normalizedDate;
        option.textContent = normalizedDate;
        dateSelector.appendChild(option);
      });

      // ✅ Auto-select today if present
      if (visitDates.some(d => d.startsWith(today))) {
        dateSelector.value = today;
      }
    } else {
      console.warn('No visit dates found:', data.message);
    }
  })
  .catch(err => {
    console.error('Error fetching visit dates:', err);
  });

  // ✅ Sync selection to date input
  dateSelector.addEventListener('change', function () {
    if (this.value) {
      dateInput.value = this.value;
    }
  });
});
</script>
                            <!-- Pregnancy Tracker Form -->
                            <form id="pregnancy-tracker-form" style="background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(44,62,80,0.08); padding: 28px 32px; display: flex; align-items: center; gap: 32px; justify-self: center;" method="POST">
                              <!-- Left Column: AOG Circle -->
                               
                              <div style="display: flex; flex-direction: column; align-items: center; min-width: 120px;">
                                <div style="position: relative; width: 90px; height: 90px; margin-bottom: 10px;">
                                  <svg width="90" height="90"><circle cx="45" cy="45" r="40" stroke="#e2e8f0" stroke-width="8" fill="none"/><circle cx="45" cy="45" r="40" stroke="#6ee7b7" stroke-width="8" fill="none" stroke-dasharray="251" stroke-dashoffset="80" stroke-linecap="round"/></svg>
                                  <div id="aogDisplay" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 18px; font-weight: 400; color: #6ee7b7; text-align: center;">0w</div>
                                </div>
                                <div style="font-size: 15px; color: #7c8ba1; font-weight: 500;">Age of Gestation</div>
                              </div>
                              
                              
                              <!-- Right Column: Inputs -->
                              <div style="display: flex; flex-direction: column; gap: 20px; flex: 1;">
                                <input  name="patient_id" type="hidden" value="<?= htmlspecialchars(PatientRecord::$id) ?>">

                                <div>
                                  <label for="lmpDate" style="color: #7c3aed; font-weight: 600; font-size: 16px; margin-bottom: 8px; display: block;">Last Menstrual Period</label>
                                  <input type="date" id="lmpDate" name="lmp_date" required style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px;">
                                </div>

                                <div>
                                  <label for="edcDate" style="color: #7c3aed; font-weight: 600; font-size: 16px; margin-bottom: 8px; display: block;">Expected Date of Confinement</label>
                                  <input type="date" id="edcDate" name="edc_date" required style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px;">
                                </div>

                                <div>
                                  <label for="notes" style="color: #7c3aed; font-weight: 600; font-size: 16px; margin-bottom: 8px; display: block;">Notes</label>
                                  <textarea id="notes" name="notes" rows="3" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px;"></textarea>
                                </div>

                                <!-- Save Button -->
                                <div style="margin-top: 10px;">
                                  <button type="submit" style="width: 100%; padding: 10px 16px; border-radius: 8px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white; border: none; cursor: pointer; font-size: 14px; font-weight: 600;">💾 SAVE</button>
                                </div>
                              </div>
                            </form>
                            <script>
                            document.getElementById('lmpDate').addEventListener('change', updateAOG);

                            function updateAOG() {
                              const lmpDate = document.getElementById('lmpDate').value;
                              const aogDisplay = document.getElementById('aogDisplay');

                              if (!lmpDate) {
                                aogDisplay.textContent = '0w';
                                return;
                              }

                              const today = new Date();
                              const lmp = new Date(lmpDate);
                              const diffDays = Math.floor((today - lmp) / (1000 * 60 * 60 * 24));
                              const aogWeeks = Math.floor(diffDays / 7);
                              aogDisplay.textContent = `${aogWeeks}w`;
                            }

                            document.getElementById('pregnancy-tracker-form').addEventListener('submit', function(e) {
                              e.preventDefault();

                              const formData = new FormData(this);
                              const payload = Object.fromEntries(formData.entries());

                              fetch('http://localhost/JAM_LYINGIN/auth/action/staff/staff_set_patient_pregnancy_tracker.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(payload)
                              })
                              .then(res => res.json())
                              .then(data => {
                                if (data.status === 'success') {
                                  alert('✅ Pregnancy tracker saved successfully.');
                                } else {
                                  alert(`❌ Error: ${data.message}`);
                                }
                              })
                              .catch(err => {
                                console.error('❌ Fetch error:', err);
                                alert('❌ Failed to save pregnancy tracker.');
                              });
                            });
                            </script>
                            <script>
                            function loadPregnancyTracker() {
                              const patientId = <?= htmlspecialchars(PatientRecord::$id) ?>;

                              fetch(`http://localhost/JAM_LYINGIN/auth/action/staff/staff_get_patient_pregnancy_tracker.php?patient_id=${patientId}`)
                                .then(res => res.json())
                                .then(data => {
                                  if (data.status === 'success') {
                                    const tracker = data.data;
                                    document.getElementById('lmpDate').value = tracker.lmp_date;
                                    document.getElementById('edcDate').value = tracker.edc_date;
                                    document.getElementById('notes').value = tracker.notes || '';
                                    updateAOG(); // Refresh AOG display
                                  } else if (data.status === 'empty') {
                                    console.log('ℹ️ No tracker found for this patient.');
                                  } else {
                                    console.warn('⚠️ Error loading tracker:', data.message);
                                  }
                                })
                                .catch(err => {
                                  console.error('❌ Fetch error:', err);
                                });
                            }

                            // Trigger on page load
                            document.addEventListener('DOMContentLoaded', loadPregnancyTracker);
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            
            <form id="riskAssessmentForm" method="POST">
              <input type="hidden" id="patientId" name="patient_id" value="<?= htmlspecialchars(PatientRecord::$id) ?>">
              <input type="hidden" id="patientRiskLevel" name="risk_level" value="notset">

              <!-- Risk Assessment Card -->
              <div style="background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(44,62,80,0.08); padding: 24px; margin-bottom: 24px; width: 100%;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                  <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                      <span style="color: #fff; font-size: 20px;">⚠️</span>
                    </div>
                    <div>
                      <div style="font-size: 18px; color: #232b3b; font-weight: 700;">Patient Risk Assessment</div>
                      <div style="font-size: 14px; color: #64748b; margin-top: 2px;">Assess and manage patient risk level</div>
                    </div>
                  </div>
                  <div id="riskLevelIndicator" style="padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; background: #e2e8f0; color: #64748b; border: 2px solid #cbd5e1;">Not Set</div>
                </div>

                <div style="margin-top: 20px;">
                  <div style="margin-bottom: 16px;">
                    <span style="font-size: 14px; color: #374151; font-weight: 600;">Select Patient Risk Level</span>
                  </div>

            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                    <button type="button"  style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 20px; border: 2px solid #cbd5e1; background: #e2e8f0; color: #64748b; cursor: pointer; transition: all 0.3s ease; font-size: 14px; font-weight: 600;" class="selected">
                                        <span>⭕</span>
                                        <span>Not Set</span>
                                    </button>
                                    <button type="button"  style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 20px; border: 2px solid #10b981; background: transparent; color: #10b981; cursor: pointer; transition: all 0.3s ease; font-size: 14px; font-weight: 600;">
                                        <span>✅</span>
                                        <span>Low Risk</span>
                                    </button>
                                    <button type="button"  style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 20px; border: 2px solid #ef4444; background: transparent; color: #ef4444; cursor: pointer; transition: all 0.3s ease; font-size: 14px; font-weight: 600;">
                                        <span>⚠️</span>
                                        <span>High Risk</span>
                                    </button>
            </div>
                </div>
              </div>
            </form>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const patientId = document.getElementById('patientId').value;
  fetch(`auth/action/staff/staff_get_patient_risk_assessment.php?patient_id=${patientId}`)
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        updateRiskUI(data.data.risk_level, false); // false = don't save again
      }
    })
    .catch(err => {
      console.error('Risk fetch error:', err);
    });
});

function updateRiskUI(level, shouldSave = true) {
  const normalized = level.toLowerCase();
  const hiddenInput = document.getElementById('patientRiskLevel');
  const indicator = document.getElementById('riskLevelIndicator');
  const buttons = document.querySelectorAll('#riskAssessmentForm button');

  buttons.forEach(btn => btn.classList.remove('selected'));

  let selectedBtn;
  switch (normalized) {
    case 'low':
      selectedBtn = buttons[1];
      indicator.textContent = 'Low Risk';
      indicator.style.background = '#d1fae5';
      indicator.style.color = '#065f46';
      indicator.style.borderColor = '#10b981';
      hiddenInput.value = 'low';
      break;
    case 'high':
      selectedBtn = buttons[2];
      indicator.textContent = 'High Risk';
      indicator.style.background = '#fee2e2';
      indicator.style.color = '#991b1b';
      indicator.style.borderColor = '#ef4444';
      hiddenInput.value = 'high';
      break;
    default:
      selectedBtn = buttons[0];
      indicator.textContent = 'Not Set';
      indicator.style.background = '#e2e8f0';
      indicator.style.color = '#64748b';
      indicator.style.borderColor = '#cbd5e1';
      hiddenInput.value = 'notset';
  }

  selectedBtn.classList.add('selected');

  if (shouldSave) {
    const formData = new FormData(document.getElementById('riskAssessmentForm'));
    fetch('auth/action/staff/staff_set_patient_risk_assessment.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.status !== 'success') {
        console.warn('Risk save failed:', data.message);
      }
    })
    .catch(err => {
      console.error('Risk save error:', err);
    });
  }
}

// Attach click handlers to buttons
document.querySelectorAll('#riskAssessmentForm button').forEach((btn, index) => {
  const levels = ['notset', 'low', 'high'];
  btn.addEventListener('click', () => updateRiskUI(levels[index], true));
});
</script>
            <!-- Visit Analytics Card - Full Width -->
            <div style="background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(44,62,80,0.08); padding: 24px 32px; margin-bottom: 24px; width: 100%;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div style="font-size: 18px; color: #232b3b; font-weight: 700;">Visit Analytics</div>
                    <form id="analyticsSearchForm" style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" class="btn-primary" id="openVisitAnalyticsModal">+ Add New</button>
                    </form>
                </div>
                <div style="overflow-x:auto;">
                    <table id="visitAnalyticsTable" style="width:100%; border-collapse:collapse; font-size:15px;">
                        <thead>
                            <tr style="background:#f8f9fe; color:#7c3aed;">
                                <th style="padding:8px 10px; text-align:left;">Visit Date</th>
                                <th style="padding:8px 10px; text-align:left;">BP</th>
                                <th style="padding:8px 10px; text-align:left;">Temp</th>
                                <th style="padding:8px 10px; text-align:left;">Weight</th>
                                <th style="padding:8px 10px; text-align:left;">Fundal Height</th>
                                <th style="padding:8px 10px; text-align:left;">Fetal Heart Tone</th>
                                <th style="padding:8px 10px; text-align:left;">Fetal Position</th>
                                <th style="padding:8px 10px; text-align:left;">Chief Complaint</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Awaiting backend analytics data -->
                        </tbody>
                    </table>
                 </div>
            </div>
                  <!-- Script for loading Analytics-->
            <script>
              console.log('📌 Visit analytics script loaded');
              function formatYMDToMDY(ymd) {
                const [y, m, d] = ymd.split('-');
                return `${m}/${d}/${y}`;
              }

              function addVisitAnalyticsToTable(visitData) {
                const tableBody = document.querySelector('#visitAnalyticsTable tbody');
                if (!tableBody) return;

                const visitDate = visitData.visit_date || new Date().toISOString().split('T')[0];
                const formattedDate = formatYMDToMDY(visitDate);

                const newRow = document.createElement('tr');
                newRow.style.borderBottom = '1px solid #e2e8f0';

                newRow.innerHTML = `
                  <td style="padding: 12px 10px; color: #1e293b; font-weight: 500;">${formattedDate}</td>
                  <td style="padding: 12px 10px; color: #64748b;">${visitData.bp || '—'}</td>
                  <td style="padding: 12px 10px; color: #64748b;">${visitData.temp || '—'}</td>
                  <td style="padding: 12px 10px; color: #64748b;">${visitData.weight || '—'}</td>
                  <td style="padding: 12px 10px; color: #64748b;">${visitData.fundal_height || '—'}</td>
                  <td style="padding: 12px 10px; color: #64748b;">${visitData.fetal_heart_tone || '—'}</td>
                  <td style="padding: 12px 10px; color: #64748b;">${visitData.fetal_position || '—'}</td>
                  <td style="padding: 12px 10px; color: #64748b;">${visitData.chief_complaint || '—'}</td>
                `;

                tableBody.appendChild(newRow);
              }
              fetch('auth/action/get_visit_analytics.php')
              .then(res => res.text())
              .then(text => console.log('Raw response:', text));

              document.addEventListener('DOMContentLoaded', () => {
                fetch('auth/action/get_visit_analytics.php')
                
                  .then(res => res.json())
                  .then(data => {
                    const tableBody = document.querySelector('#visitAnalyticsTable tbody');
                    tableBody.innerHTML = '';

                    if (!data || data.length === 0) {
                      const row = document.createElement('tr');
                      row.innerHTML = `
                        <td colspan="8" style="padding:8px 10px; text-align:center; color:#666;">
                          No visit analytics found.
                        </td>
                      `;
                      tableBody.appendChild(row);
                      return;
                    }

                    data.forEach(addVisitAnalyticsToTable);
                  })
                  .catch(err => {
                    console.error('Error loading visit analytics:', err);
                  });
              });
            </script>

            <!-- Tabs and Add New Button Row -->
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2.5px solid #e0e7ff; margin-bottom: 24px; width: 100%;">
                <div id="medrecTabs" style="display: flex; gap: 32px; align-items: center;">
                    <button type="button" id="physicalExamTab" class="medrec-tab active" style="background: none; border: none; font-size: 17px; font-weight: 700; color: #7c3aed; padding: 10px 0 12px 0; border-bottom: 3px solid #7c3aed; cursor: pointer; outline: none; transition: color 0.2s, border-bottom 0.2s;">Patient Assessment</button>
                </div>
                </div>
                <div id="physicalExamContent">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                    <h3 style="margin: 0; color: #7c3aed; font-size: 20px; font-weight: 700;">Summary</h3>
                                            <button type="button" class="btn-primary" id="physicalExamAddNewBtn" style="min-width: 120px;" onclick="console.log('Physical Exam button clicked via inline onclick')">+ Add New</button>
                </div>
                <div style="display: flex; flex-direction: column; gap: 24px; margin-bottom: 24px;">
                    <!-- Physical Exam Summary Card -->
                    <div style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); padding: 24px; transition: all 0.3s ease; position: relative; overflow: hidden; width: 100%;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">🔍</div>
                            <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Physical Examination</h3>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Conjunctiva</span>
                                <span id="summary-conjunctiva" style="color: #64748b; font-size: 14px; font-weight: 500;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Neck</span>
                                <span id="summary-neck" style="color: #64748b; font-size: 14px; font-weight: 500;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Thorax</span>
                                <span id="summary-thorax" style="color: #64748b; font-size: 14px; font-weight: 500;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Abdomen</span>
                                <span id="summary-abdomen" style="color: #64748b; font-size: 14px; font-weight: 500;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Extremities</span>
                                <span id="summary-extremities" style="color: #64748b; font-size: 14px; font-weight: 500;">—</span>
                            </div>
                            <!-- Left Breast Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">🫀 LEFT BREAST</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Mass</span>
                                        <span id="summary-breast-left-mass" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Nipple Discharge</span>
                                        <span id="summary-breast-left-nipple" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Skin Changes</span>
                                        <span id="summary-breast-left-skin" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Axillary Lymph Nodes</span>
                                        <span id="summary-breast-left-axillary" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Breast Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">🫀 RIGHT BREAST</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Mass</span>
                                        <span id="summary-breast-right-mass" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Nipple Discharge</span>
                                        <span id="summary-breast-right-nipple" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Skin Changes</span>
                                        <span id="summary-breast-right-skin" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Axillary Lymph Nodes</span>
                                        <span id="summary-breast-right-axillary" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>

                    <!-- Pelvic Examination Card -->
                    <div style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); padding: 24px; transition: all 0.3s ease; position: relative; overflow: hidden; width: 100%;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">🩺</div>
                            <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Pelvic Examination</h3>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Perinium</span>
                                <span id="summary-perinium" style="color: #64748b; font-size: 14px; font-weight: 500;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Vagina</span>
                                <span id="summary-vagina" style="color: #64748b; font-size: 14px; font-weight: 500;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">ADNEXA</span>
                                <span id="summary-adnexa" style="color: #64748b; font-size: 14px; font-weight: 500;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Cervix</span>
                                <span id="summary-cervix" style="color: #64748b; font-size: 14px; font-weight: 500;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Uterus</span>
                                <span id="summary-uterus" style="color: #64748b; font-size: 14px; font-weight: 500;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Uterine Depth</span>
                                <span id="summary-uterine-depth" style="color: #64748b; font-size: 14px; font-weight: 500;">—</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Medical History Summary Card -->
                    <div style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); padding: 24px; transition: all 0.3s ease; position: relative; overflow: hidden; width: 100%;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">📋</div>
                            <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Medical History</h3>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <!-- HEENT Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">👁️ HEENT (Head, Eyes, Ears, Nose, Throat)</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Epilepsy/Convulsion/Seizure</span>
                                        <span id="summary-epilepsy-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Severe Headache/Dizziness</span>
                                        <span id="summary-headache-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Visual Disturbance/Blurring Vision</span>
                                        <span id="summary-vision-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Yellowish Conjunctivitis</span>
                                        <span id="summary-conjunctivitis-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Enlarged Thyroid</span>
                                        <span id="summary-thyroid-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Chest/Heart Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">🫀 CHEST/HEART</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Severe Chest Pain</span>
                                        <span id="summary-chest-pain-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Shortness of Breath</span>
                                        <span id="summary-shortness-breath-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Breast/Axillary Masses</span>
                                        <span id="summary-breast-mass-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Nipple Discharge</span>
                                        <span id="summary-nipple-discharge-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Systolic ≥140</span>
                                        <span id="summary-systolic-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Diastolic ≥90</span>
                                        <span id="summary-diastolic-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Family History (CVA, HTN, Asthma, RHD)</span>
                                        <span id="summary-family-history-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Abdomen Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">🫃 ABDOMEN</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Mass in Abdomen</span>
                                        <span id="summary-abdomen-mass-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Gallbladder Disease</span>
                                        <span id="summary-gallbladder-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Liver Disease</span>
                                        <span id="summary-liver-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Genital Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">🔬 GENITAL</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Uterine Mass</span>
                                        <span id="summary-uterine-mass-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Vaginal Discharge</span>
                                        <span id="summary-vaginal-discharge-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Intermenstrual Bleeding</span>
                                        <span id="summary-intermenstrual-bleeding-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Postcoital Bleeding</span>
                                        <span id="summary-postcoital-bleeding-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Extremities Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">🦵 EXTREMITIES</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Severe Varicosities</span>
                                        <span id="summary-varicosities-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Leg Pain/Swelling</span>
                                        <span id="summary-leg-pain-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Skin Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">🫁 SKIN</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Yellowish Skin</span>
                                        <span id="summary-yellowish-skin-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>

                            <!-- History Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">📋 HISTORY</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Smoking</span>
                                        <span id="summary-smoking-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Allergies</span>
                                        <span id="summary-allergies-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Drug Intake</span>
                                        <span id="summary-drug-intake-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">STD</span>
                                        <span id="summary-std-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Multiple Partners</span>
                                        <span id="summary-multiple-partners-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Bleeding Tendencies</span>
                                        <span id="summary-bleeding-tendencies-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Anemia</span>
                                        <span id="summary-anemia-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Diabetes</span>
                                        <span id="summary-diabetes-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>

                            <!-- STI Risks Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">⚠️ STI RISKS</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Multiple Partners</span>
                                        <span id="summary-sti-multiple-partners-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Vaginal Discharge</span>
                                        <span id="summary-sti-women-discharge-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Itching/Sores</span>
                                        <span id="summary-sti-women-itching-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Pain/Burning</span>
                                        <span id="summary-sti-women-pain-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Treated for STIs</span>
                                        <span id="summary-sti-women-treated-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Open Sores</span>
                                        <span id="summary-sti-men-sores-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Pus from Penis</span>
                                        <span id="summary-sti-men-pus-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Swollen Genitals</span>
                                        <span id="summary-sti-men-swollen-notes" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Obstetrical History Summary Card -->
                    <div style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); padding: 24px; transition: all 0.3s ease; position: relative; overflow: hidden; width: 100%;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">🤱</div>
                            <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Obstetrical History</h3>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Full Term</span>
                                <span id="summary-full-term" style="color: #64748b; font-size: 14px; font-weight: 600;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Abortions</span>
                                <span id="summary-abortions" style="color: #64748b; font-size: 14px; font-weight: 600;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Premature</span>
                                <span id="summary-premature" style="color: #64748b; font-size: 14px; font-weight: 600;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Living Children</span>
                                <span id="summary-living-children" style="color: #64748b; font-size: 14px; font-weight: 600;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">Last Delivery</span>
                                <span id="summary-last-delivery" style="color: #64748b; font-size: 14px; font-weight: 600;">—</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f1f5f9; border-radius: 8px; border-left: 4px solid #667eea;">
                                <span style="color: #475569; font-weight: 600; font-size: 14px;">LMP</span>
                                <span id="summary-lmp" style="color: #64748b; font-size: 14px; font-weight: 600;">—</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- VAW Risk Summary Card -->
                    <div style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); padding: 24px; transition: all 0.3s ease; position: relative; overflow: hidden; width: 100%;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">⚠️</div>
                            <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">VAW Risk Assessment</h3>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <!-- VAW Risk Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">⚠️ VAW RISK FACTORS</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Domestic Violence</span>
                                        <span id="summary-vaw-domestic-violence" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Unpleasant Relationship</span>
                                        <span id="summary-vaw-unpleasant-relationship" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Partner Disapproves Visit</span>
                                        <span id="summary-vaw-partner-disapproves-visit" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Partner Disagrees FP</span>
                                        <span id="summary-vaw-partner-disagrees-fp" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Referred To Section -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
                                <div style="color: #475569; font-weight: 600; font-size: 14px; margin-bottom: 8px;">📋 REFERRED TO</div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 12px; background: #f1f5f9; border-radius: 6px; border-left: 3px solid #667eea;">
                                        <span style="color: #475569; font-weight: 500; font-size: 13px;">Others (Specify)</span>
                                        <span id="summary-vaw-others-specify" style="color: #64748b; font-size: 12px; font-weight: 500; max-width: 200px; text-align: right;">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

        </div>
    </div>



            </div>

    <!-- Visit Analytics Modal -->
    <div id="visitAnalyticsModal" class="modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center;">
      <div class="modal-content" style="max-width: 800px; width: 95%; background: #ffffff; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05); border-radius: 24px; padding: 0; overflow: hidden; max-height: 90vh; min-height: 500px; position: relative; display: flex; flex-direction: column;">
        
        <!-- Enhanced Header with Icon -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 32px 40px 24px 40px; position: relative; overflow: hidden; flex-shrink: 0;">
          <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255, 255, 255, 0.1); border-radius: 50%;"></div>
          <div style="position: absolute; top: 10px; right: 40px; width: 60px; height: 60px; background: rgba(255, 255, 255, 0.08); border-radius: 50%;"></div>
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <span style="color: #fff; font-size: 24px;">📊</span>
            </div>
            <div>
              <h2 style="color: #fff; font-size: 28px; font-weight: 800; margin: 0; letter-spacing: -0.5px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Visit Analytics</h2>
              <p style="color: rgba(255, 255, 255, 0.9); font-size: 16px; margin: 4px 0 0 0; font-weight: 400;">Record patient visit measurements and observations</p>
            </div>
          </div>
        </div>
              
        <!-- Enhanced Form -->
        <form id="visitAnalyticsForm" style="padding: 40px; background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%); overflow-y: auto; flex: 1; scrollbar-width: thin; scrollbar-color: #cbd5e1 #f1f5f9;">
          
          <!-- Visit Date Section - Compact Size -->
           
          <div style="margin-bottom: 24px; padding: 16px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 12px; border: 1px solid #e2e8f0;">
            <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 8px; display: block; letter-spacing: -0.3px;">📅 Visit Date</label>
            <div id="selectedVisitDateDisplay" style="padding: 12px 16px; border-radius: 8px; border: 2px solid #cbd5e1; font-size: 16px; background: #ffffff; color: #475569; font-weight: 600; text-align: center; box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.06);">--</div>
            <input type="hidden" name="visit_date" id="hiddenVisitDate">
            <p style="color: #64748b; font-size: 13px; margin: 6px 0 0 0; font-style: italic;">Date selected from Search Medical Records</p>
          </div>
          <input  name="patient_id" value="<?= htmlspecialchars(PatientRecord::$id) ?>">
          <!-- Form Grid -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px 32px; margin-bottom: 32px;">
            
            <!-- BP Field -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #ef4444;">❤️</span> Blood Pressure
              </label>
              <input type="text" name="bp" placeholder="e.g., 120/80" required style="padding: 16px 20px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 16px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            </div>

            <!-- Temperature Field -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #f59e0b;">🌡️</span> Temperature
              </label>
              <input type="text" name="temp" placeholder="e.g., 98.6°F" required style="padding: 16px 20px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 16px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            </div>

            <!-- Weight Field -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #10b981;">⚖️</span> Weight
              </label>
              <input type="text" name="weight" placeholder="e.g., 65 kg" required style="padding: 16px 20px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 16px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            </div>

            <!-- Fundal Height Field -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #8b5cf6;">📏</span> Fundal Height
              </label>
              <input type="text" name="fundal_height" placeholder="e.g., 24 cm" required style="padding: 16px 20px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 16px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            </div>

            <!-- Fetal Heart Tone Field -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #ec4899;">💓</span> Fetal Heart Tone
              </label>
              <input type="text" name="fetal_heart_tone" placeholder="e.g., 140 bpm" required style="padding: 16px 20px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 16px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            </div>

            <!-- Fetal Position Field -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #06b6d4;">👶</span> Fetal Position
              </label>
              <input type="text" name="fetal_position" placeholder="e.g., Cephalic" required style="padding: 16px 20px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 16px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            </div>
          </div>

          <!-- Chief Complaint - Full Width -->
          <div style="margin-bottom: 32px;">
            <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
              <span style="color: #f97316;">📝</span> Chief Complaint
            </label>
            <input type="text" name="chief_complaint" placeholder="Describe the main reason for visit..." required style="width: 100%; padding: 16px 20px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 16px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
          </div>

          <!-- Enhanced Action Buttons -->
          <div style="display: flex; justify-content: flex-end; gap: 16px; padding-top: 24px; border-top: 1px solid #e2e8f0; flex-shrink: 0;">
            <button type="button" id="closeVisitAnalyticsModal" style="padding: 14px 28px; border-radius: 12px; font-size: 15px; font-weight: 600; background: #f8fafc; color: #475569; border: 2px solid #e2e8f0; cursor: pointer; transition: all 0.3s ease; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; min-width: 120px;">Cancel</button>
            <button type="submit" style="padding: 14px 32px; border-radius: 12px; font-size: 16px; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 14px rgba(102, 126, 234, 0.4); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; min-width: 140px;">💾 Save Analytics</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Physical Examination Add New Modal -->
    <div id="physicalExamModal" class="modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center;">
      <div class="modal-content" style="max-width: 800px; width: 95%; background: #ffffff; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05); border-radius: 24px; padding: 0; overflow: hidden; max-height: 90vh; min-height: 500px; position: relative; display: flex; flex-direction: column;">
        
        <!-- Enhanced Header with Icon -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 32px 40px 24px 40px; position: relative; overflow: hidden; flex-shrink: 0;">
          <div style="position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; background: rgba(255, 255, 255, 0.1); border-radius: 50%;"></div>
          <div style="position: absolute; top: 10px; right: 40px; width: 60px; height: 60px; background: rgba(255, 255, 255, 0.08); border-radius: 50%;"></div>
          <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
          <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
              <span style="color: #fff; font-size: 24px;">🔍</span>
            </div>
            <div>
                <h2 style="color: #fff; font-size: 28px; font-weight: 800; margin: 0; letter-spacing: -0.5px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Patient Assessment</h2>
                <p style="color: rgba(255, 255, 255, 0.9); font-size: 16px; margin: 4px 0 0 0; font-weight: 400;">Record patient medical history and physical examination findings</p>
            </div>
            </div>
            <button type="button" onclick="bringModalToTop()" style="padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; background: rgba(255, 255, 255, 0.2); color: white; border: 1px solid rgba(255, 255, 255, 0.3); cursor: pointer; transition: all 0.3s ease; backdrop-filter: blur(10px); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;" onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='translateY(0)'">
              ⬆️ Bring to Top
            </button>
          </div>
        </div>

        <!-- Enhanced Form -->
        <form id="physicalExamForm"   style="padding: 40px; background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%); overflow-y: auto; flex: 1; scrollbar-width: thin; scrollbar-color: #cbd5e1 #f1f5f9;">
          
        <div id="physicalExamPage1">
            <!-- Visit Date Section - Compact Size -->
            <div style="margin-bottom: 24px; padding: 16px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 12px; border: 1px solid #e2e8f0;">
              <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 8px; display: block; letter-spacing: -0.3px;">📅 Visit Date</label>
              <div id="physicalExamVisitDateDisplay" style="padding: 12px 16px; border-radius: 8px; border: 2px solid #cbd5e1; font-size: 16px; background: #ffffff; color: #475569; font-weight: 600; text-align: center; box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.06);">--</div>
              <input type="hidden" name="exam_date" id="physicalExamHiddenVisitDate"> <!-- Get The date from here -->
              
              <p style="color: #64748b; font-size: 13px; margin: 6px 0 0 0; font-style: italic;">Date selected from Search Medical Records</p>
            </div>

            <!-- Tab Navigation -->
            <div style="display: flex; gap: 8px; margin-bottom: 32px; border-bottom: 2px solid #e2e8f0; padding-bottom: 0;">
              <button type="button" class="patient-assessment-tab active" data-tab="physical-examination" style="background: none; border: none; font-size: 16px; font-weight: 600; color: #667eea; padding: 16px 24px; border-bottom: 3px solid #667eea; cursor: pointer; outline: none; transition: all 0.3s ease; border-radius: 8px 8px 0 0;">Physical Examination</button>
              <button type="button" class="patient-assessment-tab" data-tab="medical-history" style="background: none; border: none; font-size: 16px; font-weight: 500; color: #64748b; padding: 16px 24px; border-bottom: 3px solid transparent; cursor: pointer; outline: none; transition: all 0.3s ease; border-radius: 8px 8px 0 0;">Medical History</button>
              <button type="button" class="patient-assessment-tab" data-tab="obstetrical-history" style="background: none; border: none; font-size: 16px; font-weight: 500; color: #64748b; padding: 16px 24px; border-bottom: 3px solid transparent; cursor: pointer; outline: none; transition: all 0.3s ease; border-radius: 8px 8px 0 0;">Obstetrical History</button>
              <button type="button" class="patient-assessment-tab" data-tab="vaw-risk" style="background: none; border: none; font-size: 16px; font-weight: 500; color: #64748b; padding: 16px 24px; border-bottom: 3px solid transparent; cursor: pointer; outline: none; transition: all 0.3s ease; border-radius: 8px 8px 0 0;">VAW Risk</button>
            </div>

            <!-- Tab Content -->
            <!-- Tab 1: Physical Examination -->
            <div id="physical-examination-content" class="tab-content active">
            <!-- Physical Examination Section -->
            <div style="margin-bottom: 32px;">
              <input  name="patient_id" type="hidden" value="<?= htmlspecialchars(PatientRecord::$id) ?>">
              <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #8b5cf6;">🔍</span> Physical Examination
              </label>
              
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px 32px;">
                  <input type="hidden" name="staff_id" value="<?= htmlspecialchars($_SESSION['user_id'] ?? 0) ?>">
                  <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">Doctor's Note</span>
                  <textarea name="doctor_note" id="doctor_note" rows="4" cols="50" placeholder="Enter summary or remarks here..."></textarea>
              </div>
                

                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">👁️ Conjunctiva</span>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="conjunctiva[]" value="Pale" style="accent-color: #667eea;"> Pale</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="conjunctiva[]" value="Yellowish" style="accent-color: #667eea;"> Yellowish</label>
                </div>
                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">🦴 Neck</span>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="neck[]" value="Thyroid" style="accent-color: #667eea;"> Enlarged Thyroid</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="neck[]" value="Nodes" style="accent-color: #667eea;"> Enlarged Lymph Nodes</label>
                </div>
                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">👙 Breast (Left)</span>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="breast_left[]" value="mass" style="accent-color: #667eea;"> Mass</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="breast_left[]" value="nipple" style="accent-color: #667eea;"> Nipple Discharge</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="breast_left[]" value="skin" style="accent-color: #667eea;"> Skin: orange peel or dimpling</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="breast_left[]" value="axillary" style="accent-color: #667eea;"> Enlarged axillary lymph nodes</label>
                </div>
                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">👙 Breast (Right)</span>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="breast_right[]" value="mass" style="accent-color: #667eea;"> Mass</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="breast_right[]" value="nipple" style="accent-color: #667eea;"> Nipple Discharge</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="breast_right[]" value="skin" style="accent-color: #667eea;"> Skin: orange peel or dimpling</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="breast_right[]" value="axillary" style="accent-color: #667eea;"> Enlarged axillary lymph nodes</label>
                </div>
                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">🫁 Thorax</span>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="thorax[]" value="heart" style="accent-color: #667eea;"> Abnormal heart sounds/cardiac rate</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="thorax[]" value="breath" style="accent-color: #667eea;"> Abnormal breath sounds/respiratory rate</label>
                </div>
                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">🫃 Abdomen</span>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="abdomen[]" value="liver" style="accent-color: #667eea;"> Enlarged Liver</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="abdomen[]" value="mass" style="accent-color: #667eea;"> Mass</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="abdomen[]" value="tenderness" style="accent-color: #667eea;"> Tenderness</label>
                </div>
                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">🦵 Extremities</span>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="extremities[]" value="edema" style="accent-color: #667eea;"> Edema</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="extremities[]" value="varicosities" style="accent-color: #667eea;"> Varicosities</label>
                </div>
              </div>
            </div>
                                  
            <!-- Pelvic Examination Section -->
            <div style="margin-bottom: 32px;">
              <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #8b5cf6;">🔬</span> Pelvic Examination
              </label>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px 32px;">
              <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">🔍 Perinium</span>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="perinium[]" value="scars" style="accent-color: #667eea;"> Scars</label>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="perinium[]" value="warts" style="accent-color: #667eea;"> Warts</label>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="perinium[]" value="reddish" style="accent-color: #667eea;"> Reddish</label>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="perinium[]" value="lacerations" style="accent-color: #667eea;"> Lacerations</label>
              </div>
              <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">🔬 Vagina</span>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="vagina[]" value="congested" style="accent-color: #667eea;"> Congested</label>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="vagina[]" value="cyst" style="accent-color: #667eea;"> Bartholin's Cyst</label>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="vagina[]" value="warts" style="accent-color: #667eea;"> Warts</label>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="vagina[]" value="gland" style="accent-color: #667eea;"> Skene's Gland Discharge</label>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="vagina[]" value="recto" style="accent-color: #667eea;"> Rectocoele</label>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="vagina[]" value="cysto" style="accent-color: #667eea;"> Cystocoele</label>
              </div>
              <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                 <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">🔬 Cervix</span>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="cervix[]" value="congested" style="accent-color: #667eea;">Congested</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="cervix[]" value="erosion" style="accent-color: #667eea;">Erosion</label>
                  <span style="color: #1e293b; font-weight: 500; display: block; margin: 12px 0 8px 0; font-size: 13px;">Consistency:</span>
                    <select name="cervix_consistency">
                      <option value="">--Select--</option>
                      <option value="Soft" selected>Soft</option>
                      <option value="Firm">Firm</option>
                    </select>
                </div>
                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">🫃 Uterus</span>
                  <span style="color: #1e293b; font-weight: 500; display: block; margin: 12px 0 8px 0; font-size: 13px;">Position:</span>
                    <select name="uterus_position">
                      <option value="">--Select--</option>
                      <option value="Mid" selected>Mid</option>
                      <option value="Anteflexed">Anteflexed</option>
                      <option value="Retroflexed">Retroflexed</option>
                    </select>
                  <span style="color: #1e293b; font-weight: 500; display: block; margin: 12px 0 8px 0; font-size: 13px;">Size:</span>
                    <select name="uterus_size">
                      <option value="">--Select--</option>
                      <option value="Small">Small</option>
                      <option value="Normal" selected>Normal</option>
                      <option value="Large">Large</option>
                    </select>
                  <span style="color: #1e293b; font-weight: 500; display: block; margin: 16px 0 8px 0; font-size: 13px;">Uterine Depth:</span>
                  <input type="text" name="uterine_depth_cm" placeholder="e.g., 7 cm" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                </div>
                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">🔬 ADNEXA</span>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="adnexa[]" value="mass" style="accent-color: #667eea;"> Mass</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="adnexa[]" value="tenderness" style="accent-color: #667eea;"> Tenderness</label>
                </div>
              </div>
            </div>
                <div style="text-align: right; margin-top: 24px;">
                <button type="submit"  style="
                  padding: 12px 24px;
                  background-color: #10b981;
                  color: white;
                  border: none;
                  border-radius: 8px;
                  font-weight: bold;
                  cursor: pointer;
                ">
                  💾 Save Patient Exam
                </button>
              </div>
          </div>
          
        </form>

            <!-- Tab 2: Medical History -->
          <div id="medical-history-content" class="tab-content" style="display: none;">
            <!-- HEENT Section --> 
            <form id="medicalHistoryForm" >
 
            <input  name="visit_date" id="medicalHistoryHiddenVisitDate" type="hidden"> <!-- Get The date from here -->
            <input  name="patient_id" value="<?= htmlspecialchars(PatientRecord::$id) ?>" type = hidden>
                <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
                  <input type="hidden" name="staff_id" value="<?= htmlspecialchars($_SESSION['user_id'] ?? 0) ?>">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">Doctor's Note</span>
                  <textarea name="doctor_note" rows="4" cols="50" placeholder="Enter summary or remarks here..."></textarea>
              </div> 
            <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
              <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">👁️ HEENT</h3>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                  
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="heent[]" value="epilepsy" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'epilepsy-notes')"> Epilepsy/Convulsion/Seizure
                  </label>
                  <input type="text" name="epilepsy_notes" id="epilepsy-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                  
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="heent[]" value="headache" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'headache-notes')"> Severe Headache/Dizziness
                  </label>
                  <input type="text" name="headache_notes" id="headache-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                  
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="heent[]" value="vision" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'vision-notes')"> Visual Disturbance/Blurring Vision
                  </label>
                  <input type="text" name="vision_notes" id="vision-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                </div>
                <div>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="heent[]" value="conjunctivitis" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'conjunctivitis-notes')"> Yellowish Conjuctivitis
                  </label>
                  <input type="text" name="conjunctivitis_notes" id="conjunctivitis-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                  
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="heent[]" value="thyroid" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'thyroid-notes')"> Enlarged Thyroid
                  </label>
                  <input type="text" name="thyroid_notes" id="thyroid-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                </div>
              </div>
            </div>
     
            <!-- CHEST/HEART Section -->
            <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
              <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">🫀 CHEST/HEART</h3>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="chest_heart[]" value="chest_pain" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'chest-pain-notes')"> Severe Chest Pain
                  </label>
                  <input type="text" name="chest_pain_notes" id="chest-pain-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                  
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="chest_heart[]" value="shortness_breath" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'shortness-breath-notes')"> Shortness of breath and easy fatigibility
                  </label>
                  <input type="text" name="shortness_breath_notes" id="shortness-breath-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                  
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="chest_heart[]" value="breast_mass" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'breast-mass-notes')"> Breast/Axillary Masses
                  </label>
                  <input type="text" name="breast_mass_notes" id="breast-mass-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                  
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="chest_heart[]" value="nipple_discharge" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'nipple-discharge-notes')"> Nipple Discharge (specify if blood or pus)
                  </label>
                  <input type="text" name="nipple_discharge_notes" id="nipple-discharge-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                </div>
                <div>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="chest_heart[]" value="systolic" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'systolic-notes')"> Systolic of 140 & above
                  </label>
                  <input type="text" name="systolic_notes" id="systolic-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                  
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="chest_heart[]" value="diastolic" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'diastolic-notes')"> Diastolic of 90 & above
                  </label>
                  <input type="text" name="diastolic_notes" id="diastolic-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                  
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                    <input type="checkbox" name="chest_heart[]" value="family_history" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'family-history-notes')"> Family History of CVA (strokes), Hypertension, Asthma, Rheumatic Heart Disease
                  </label>
                  <input type="text" name="family_history_notes" id="family-history-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                </div>
              </div>
                         </div>

             <!-- ABDOMEN Section -->
             <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
               <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">🫃 ABDOMEN</h3>
               <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="abdomen[]" value="mass" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'abdomen-mass-notes')"> Mass in the Abdomen
                   </label>
                   <input type="text" name="abdomen_mass_notes" id="abdomen-mass-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="abdomen[]" value="gallbladder" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'gallbladder-notes')"> History of Gallbladder Disease
                   </label>
                   <input type="text" name="gallbladder_notes" id="gallbladder-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="abdomen[]" value="liver" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'liver-notes')"> History of Liver Disease
                   </label>
                   <input type="text" name="liver_notes" id="liver-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
               </div>
             </div>

             <!-- GENITAL Section -->
             <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
               <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">🔬 GENITAL</h3>
               <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="genital[]" value="uterine_mass" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'uterine-mass-notes')"> Mass in the Uterus
                   </label>
                   <input type="text" name="uterine_mass_notes" id="uterine-mass-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="genital[]" value="vaginal_discharge" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'vaginal-discharge-notes')"> Vaginal Discharge
                   </label>
                   <input type="text" name="vaginal_discharge_notes" id="vaginal-discharge-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="genital[]" value="intermenstrual_bleeding" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'intermenstrual-bleeding-notes')"> Intermenstrual Bleeding
                   </label>
                   <input type="text" name="intermenstrual_bleeding_notes" id="intermenstrual-bleeding-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="genital[]" value="postcoital_bleeding" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'postcoital-bleeding-notes')"> Postcoital Bleeding
                   </label>
                   <input type="text" name="postcoital_bleeding_notes" id="postcoital-bleeding-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
               </div>
             </div>

             <!-- EXTREMITIES Section -->
             <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
               <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">🦵 EXTREMITIES</h3>
               <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="extremities[]" value="varicosities" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'varicosities-notes')"> Severe Varicosities
                   </label>
                   <input type="text" name="varicosities_notes" id="varicosities-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="extremities[]" value="leg_pain" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'leg-pain-notes')"> Swelling or Severe Pain in the Legs Not Related To Injuries
                   </label>
                   <input type="text" name="leg_pain_notes" id="leg-pain-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
               </div>
             </div>

             <!-- SKIN Section -->
             <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
               <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">🫁 SKIN</h3>
               <div>
                 <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                   <input type="checkbox" name="skin[]" value="yellowish" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'yellowish-skin-notes')"> Yellowish Skin
                 </label>
                 <input type="text" name="yellowish_skin_notes" id="yellowish-skin-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
               </div>
             </div>

             <!-- HISTORY OF THE FOLLOWING Section -->
             <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
               <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">📋 HISTORY OF THE FOLLOWING</h3>
               <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="history[]" value="smoking" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'smoking-notes')"> Smoking
                   </label>
                   <input type="text" name="smoking_notes" id="smoking-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="history[]" value="allergies" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'allergies-notes')"> Allergies
                   </label>
                   <input type="text" name="allergies_notes" id="allergies-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="history[]" value="drug_intake" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'drug-intake-notes')"> Drug intake (anti-tuberculosis, anti-diabetic, anticonvulsant)
                   </label>
                   <input type="text" name="drug_intake_notes" id="drug-intake-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="history[]" value="std" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'std-notes')"> STD
                   </label>
                   <input type="text" name="std_notes" id="std-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="history[]" value="multiple_partners" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'multiple-partners-notes')"> Multiple Partners
                   </label>
                   <input type="text" name="multiple_partners_notes" id="multiple-partners-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="history[]" value="bleeding_tendencies" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'bleeding-tendencies-notes')"> Bleeding Tendencies (nose, gums, etc.)
                   </label>
                   <input type="text" name="bleeding_tendencies_notes" id="bleeding-tendencies-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="history[]" value="anemia" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'anemia-notes')"> Anemia
                   </label>
                   <input type="text" name="anemia_notes" id="anemia-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="history[]" value="diabetes" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'diabetes-notes')"> Diabetes
                   </label>
                   <input type="text" name="diabetes_notes" id="diabetes-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
               </div>
             </div>

             <!-- STI RISKS Section -->

             <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
               <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">⚠️ STI RISKS</h3>
               
               <!-- General STI Risk -->
               <div style="margin-bottom: 20px;">
                 <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                   <input type="checkbox" name="sti_risks[]" value="multiple_partners" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'sti-multiple-partners-notes')"> With History of Multiple Partners
                 </label>
                 <input type="text" name="sti_multiple_partners_notes" id="sti-multiple-partners-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
               </div>

               <!-- For Women -->
               <div style="margin-bottom: 20px;">
                 <h4 style="color: #1e293b; font-weight: 600; font-size: 16px; margin-bottom: 16px;">For Women:</h4>
                 <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                   <div>
                     <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                       <input type="checkbox" name="sti_women[]" value="unusual_discharge" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'sti-women-discharge-notes')"> Unusual Discharge from Vagina
                     </label>
                     <input type="text" name="sti_women_discharge_notes" id="sti-women-discharge-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                     
                     <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                       <input type="checkbox" name="sti_women[]" value="itching_sores" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'sti-women-itching-notes')"> Itching or Sores In or Around Vagina
                     </label>
                     <input type="text" name="sti_women_itching_notes" id="sti-women-itching-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                     
                     <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                       <input type="checkbox" name="sti_women[]" value="pain_burning" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'sti-women-pain-notes')"> Pain or Burning Sensation
                     </label>
                     <input type="text" name="sti_women_pain_notes" id="sti-women-pain-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   </div>
                   <div>
                     <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                       <input type="checkbox" name="sti_women[]" value="treated_sti" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'sti-women-treated-notes')"> Treated for STIs in the Past
                     </label>
                     <input type="text" name="sti_women_treated_notes" id="sti-women-treated-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   </div>
                 </div>
               </div>

               <!-- For Men -->
               <div>
                 <h4 style="color: #1e293b; font-weight: 600; font-size: 16px; margin-bottom: 16px;">For Men:</h4>
                 <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                   <div>
                     <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                       <input type="checkbox" name="sti_men[]" value="pain_burning" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'sti-men-pain-notes')"> Pain or Burning Sensation
                     </label>
                     <input type="text" name="sti_men_pain_notes" id="sti-men-pain-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                     
                     <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                       <input type="checkbox" name="sti_men[]" value="open_sores" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'sti-men-sores-notes')"> Open Sores Anywhere in Genital Area
                     </label>
                     <input type="text" name="sti_men_sores_notes" id="sti-men-sores-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   </div>
                   <div>
                     <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                       <input type="checkbox" name="sti_men[]" value="pus_penis" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'sti-men-pus-notes')"> Pus Coming From Penis
                     </label>
                     <input type="text" name="sti_men_pus_notes" id="sti-men-pus-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                     
                     <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                       <input type="checkbox" name="sti_men[]" value="swollen_genitals" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'sti-men-swollen-notes')"> Swollen Testicles or Penis
                     </label>
                     <input type="text" name="sti_men_swollen_notes" id="sti-men-swollen-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                     
                     <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                       <input type="checkbox" name="sti_men[]" value="treated_sti" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'sti-men-treated-notes')"> Treated for STIs in the Past
                     </label>
                     <input type="text" name="sti_men_treated_notes" id="sti-men-treated-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   </div>
                 </div>
               </div>
                		<div style="text-align: right; margin-top: 24px;">
                <button type="submit" style="
                  padding: 12px 24px;
                  background-color: #10b981;
                  color: white;
                  border: none;
                  border-radius: 8px;
                  font-weight: bold;
                  cursor: pointer;
                ">
                  💾 Submit Medical History
                </button>
              </div>
            </form>
             </div>


            </div>
                                       
            <!-- Tab 3: Obstetrical History -->
           <div id="obstetrical-history-content" class="tab-content" style="display: none;">
           <form id="obstetricalHistoryForm">  
            <input  name="visit_date" id="obstetricalHistoryHiddenDate" type="hidden"> <!-- Get The date from here -->
            <input  name="patient_id" value="<?= htmlspecialchars(PatientRecord::$id) ?>" type="hidden">
              <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
                  <input type="hidden" name="staff_id" value="<?= htmlspecialchars($_SESSION['user_id'] ?? 0) ?>">
                   <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">Doctor's Note</span>
                  <textarea name="doctor_note" id="doctor_note" rows="4" cols="50" placeholder="Enter summary or remarks here..."></textarea>
              </div>

           <!-- NUMBER OF PREGNANCIES Section -->
             <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
               <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">🤱 NUMBER OF PREGNANCIES</h3>
               <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                 <div>
                   <label style="color: #1e293b; font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Full term</label>
                   <input type="number" name="full_term" min="0" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                 </div>
                 <div>
                   <label style="color: #1e293b; font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Abortions</label>
                   <input type="number" name="abortions" min="0" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                 </div>
                 <div>
                   <label style="color: #1e293b; font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Premature</label>
                   <input type="number" name="premature" min="0" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                 </div>
                 <div>
                   <label style="color: #1e293b; font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Living Children</label>
                   <input type="number" name="living_children" min="0" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                 </div>
               </div>
             </div>

             <!-- Additional Obstetrical Information -->
             <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
               <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">📅 ADDITIONAL INFORMATION</h3>
               <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                 <div>
                   <label style="color: #1e293b; font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Date of Last Delivery</label>
                   <input type="date" name="last_delivery_date" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                 </div>
                 <div>
                   <label style="color: #1e293b; font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Type of Last Delivery</label>
                   <select name="last_delivery_type" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                     <option value="">Select type</option>
                     <option value="Normal">Normal</option>
                     <option value="Cesarean">Cesarean</option>
                     <option value="Forceps">Forceps</option>
                     <option value="Vacuum">Vacuum</option>
                   </select>
                 </div>
                 <div>
                   <label style="color: #1e293b; font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Past Menstrual Period</label>
                   <input type="date" name="past_menstrual_period" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                 </div>
                 <div>
                   <label style="color: #1e293b; font-weight: 600; font-size: 14px; margin-bottom: 8px; display: block;">Duration and Character of Menstrual Bleeding</label>
                   <input type="text" name="menstrual_character" placeholder="e.g., 5 days, heavy flow" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
                 </div>
               </div>
             </div>

             <!-- HISTORY OF THE FOLLOWING Section -->
             <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
               <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">📋 HISTORY OF THE FOLLOWING</h3>
               <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="obstetrical_history[]" value="hydatidiform_mole" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'hydatidiform-mole-notes')"> Hydatidiform Mole (within the last 12 months)
                   </label>
                   <input type="text" name="hydatidiform_mole_notes" id="hydatidiform-mole-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="obstetrical_history[]" value="ectopic_pregnancy" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'ectopic-pregnancy-notes')"> Ectopic Pregnancy
                   </label>
                   <input type="text" name="ectopic_pregnancy_notes" id="ectopic-pregnancy-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
               </div>
             </div>
              		<div style="text-align: right; margin-top: 24px;">
                <button type="submit"  style="
                  padding: 12px 24px;
                  background-color: #10b981;
                  color: white;
                  border: none;
                  border-radius: 8px;
                  font-weight: bold;
                  cursor: pointer;
                ">
                  💾 Save Obstetrical History
                </button>
              </div>
            </form>
           </div>

            <!-- Tab 4: VAW Risk -->
           <form id="vaw-risk-form">
          
           <div id="vaw-risk-content" class="tab-content" style="display: none;">
             <!-- VAW Risk Assessment -->
            <input  name="visit_date" id="vawExamHiddenVisitDate" type="hidden">
            <input  name="patient_id" value="<?= htmlspecialchars(PatientRecord::$id) ?>" type="hidden">
              <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
                  <input type="hidden" name="staff_id" value="<?= htmlspecialchars($_SESSION['user_id'] ?? 0) ?>">
                   <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">Doctor's Note</span>
                  <textarea name="doctor_note" id="doctor_note" rows="4" cols="50" placeholder="Enter summary or remarks here..."></textarea>
              </div>

             <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
               <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">⚠️ RISK FOR VIOLENCE AGAINST WOMEN (VAW)</h3>
               <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="vaw_risk[]" value="domestic_violence" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'domestic-violence-notes')"> History of Domestic Violence or VAW
                   </label>
                   <input type="text" name="domestic_violence_notes" id="domestic-violence-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="vaw_risk[]" value="unpleasant_relationship" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'unpleasant-relationship-notes')"> Unpleasant Relationship with Partner
                   </label>
                   <input type="text" name="unpleasant_relationship_notes" id="unpleasant-relationship-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="vaw_risk[]" value="partner_disapproves_visit" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'partner-disapproves-visit-notes')"> Partner Does Not Approve Of The Visit to FP Clinic
                   </label>
                   <input type="text" name="partner_disapproves_visit_notes" id="partner-disapproves-visit-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                   
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="vaw_risk[]" value="partner_disagrees_fp" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'partner-disagrees-fp-notes')"> Partner Disagrees to Use FP
                   </label>
                   <input type="text" name="partner_disagrees_fp_notes" id="partner-disagrees-fp-notes" placeholder="Enter notes..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
               </div>
             </div>

             <!-- Referred to Section -->

             <div style="background: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
               <h3 style="color: #1e293b; font-weight: 700; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">🔗 REFERRED TO</h3>
               <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="referred_to[]" value="dswd" style="accent-color: #667eea;"> DSWD
                   </label>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="referred_to[]" value="wcpu" style="accent-color: #667eea;"> WCPU
                   </label>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="referred_to[]" value="ngos" style="accent-color: #667eea;"> NGOs
                   </label>
                 </div>
                 <div>
                   <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 12px; color: #475569;">
                     <input type="checkbox" name="referred_to[]" value="others" style="accent-color: #667eea;" onchange="toggleNotesInput(this, 'others-specify-notes')"> Others (specify)
                   </label>
                   <input type="text" name="others_specify_notes" id="others-specify-notes" placeholder="Enter details..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 14px; background: #ffffff; display: none; margin-bottom: 8px;">
                 </div>
               </div>
             </div>
              <div style="text-align: right; margin-top: 24px;">
                <button type="button" id="submit-vaw-btn" style="
                  padding: 12px 24px;
                  background-color: #10b981;
                  color: white;
                  border: none;
                  border-radius: 8px;
                  font-weight: bold;
                  cursor: pointer;
                ">
                  💾 Save VAW Assessment
                </button>
              </div>
           </div>
           </form>


                        <!-- Save and Close Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 16px; padding-top: 24px; border-top: 1px solid #e2e8f0; flex-shrink: 0; margin-bottom: 0;">
              <!-- <button type="button" onclick="saveAndUpdateSummary()" style="padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 500; background: linear-gradient(90deg, #10b981 0%, #059669 100%); color: white; border: none; cursor: pointer; transition: all 0.2s ease; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; min-width: 160px; box-shadow: none;" onmouseover="this.style.background='linear-gradient(90deg, #059669 0%, #047857 100%)'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='linear-gradient(90deg, #10b981 0%, #059669 100%)'; this.style.transform='translateY(0)'">💾 Save & Update Summary</button> -->
              <button type="button" id="closePhysicalExamModal" style="padding: 14px 28px; border-radius: 12px; font-size: 15px; font-weight: 600; background: #f8fafc; color: #475569; border: 2px solid #e2e8f0; cursor: pointer; transition: all 0.3s ease; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; min-width: 120px;">Done</button>
            </div>
          
          </div>
          
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const medicalHistoryForm = document.getElementById('medicalHistoryForm');
  console.log('<?= htmlspecialchars($_SESSION['user_id'] ?? 0) ?> hakdog'); 
  if (medicalHistoryForm) {
    medicalHistoryForm.addEventListener('submit', function (e) {
      e.preventDefault();
      e.stopPropagation();

      const formData = new FormData(medicalHistoryForm);

      fetch('auth/action/submit_medical_history.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        console.log('📦 Response:', data); // Optional debug

        if (data.success) {
          alert('Medical history saved successfully. ID: ' + data.record_id);
        } else {
          alert('Submission failed: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the form.');
      });

      return false;
    });
  }
});

</script>

    <script>
        // CRITICAL FUNCTIONS - Define these first to ensure they're accessible
        window.saveAndCalculateAOG = function(event) {
            console.log('Save and calculate triggered');
            
            // Get the button that was clicked
            const saveBtn = event ? event.target : document.querySelector('button[onclick*="saveAndCalculateAOG"]');
            
            const lmpDateInput = document.getElementById('lmpDate');
            console.log('LMP input found:', lmpDateInput);
            console.log('LMP input value:', lmpDateInput ? lmpDateInput.value : 'null');
            
            if (lmpDateInput && lmpDateInput.value) {
                console.log('Saving and calculating with LMP:', lmpDateInput.value);
                
                // Calculate AOG and EDC
                if (typeof calculateAgeOfGestation === 'function') {
                    calculateAgeOfGestation();
                } else {
                    console.error('calculateAgeOfGestation function not found');
                }
                
                // Show success message
                if (saveBtn) {
                    const originalText = saveBtn.textContent;
                    saveBtn.textContent = '✓ SAVED!';
                    saveBtn.style.background = 'linear-gradient(90deg, #059669 0%, #047857 100%)';
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        saveBtn.textContent = originalText;
                        saveBtn.style.background = 'linear-gradient(90deg, #667eea 0%, #764ba2 100%)';
                    }, 2000);
                }
                
                console.log('AOG and EDC saved and calculated successfully');
            } else {
                console.log('No LMP date entered for saving');
                alert('Please enter a Last Menstrual Period date first.');
            }
        };

        // Method 1: Simple client-side sign out
        function signOut() {
            // Clear any stored user data
            localStorage.clear();
            sessionStorage.clear();
            
            // Show confirmation dialog
            if (confirm('Are you sure you want to sign out?')) {
        // Redirect to logout.php which will handle session cleanup
        window.location.href = 'logout.php';
    }
        }

        // Method 2: More advanced sign out with server communication
        function signOutAdvanced() {
            if (confirm('Are you sure you want to sign out?')) {
                // Send request to server to invalidate session
                fetch('/api/logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include' // Include cookies
                })
                .then(() => {
                    // Clear local storage
                    localStorage.clear();
                    sessionStorage.clear();
                    
                    // Redirect to login page
                    window.location.href = '/new lying-in/front.html';
                })
                .catch(error => {
                    console.error('Sign out error:', error);
                    // Still redirect even if server request fails
                    window.location.href = '/new lying-in/front.html';
                });
            }
        }
        // Add some basic interactivity
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('.card-title').textContent;
                alert(`Opening ${title} module...`);
            });
        });

        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                
                // Debug: Log what tab was clicked
                console.log('Tab clicked:', this.textContent.trim());
                
                // Toggle dashboard, patient list, medical records, and services
                const mainContent = document.querySelector('.main-content > .content-section');
                const patientList = document.getElementById('patient-list-section');
                const medRecords = document.getElementById('medical-records-section');
                const settings = document.getElementById('settings-section');
                // Debug: Log the elements found
                console.log('Main content:', mainContent);
                console.log('Patient list:', patientList);
                console.log('Medical records:', medRecords);
                console.log('Settings', settings)
                
                if (this.textContent.trim().includes('Patients')) {
                    console.log('Showing Patients section');
                    mainContent.style.display = 'none';
                    settings.style.display = 'none';
                    patientList.style.display = '';
                    medRecords.style.display = 'none';
                } else if (this.textContent.trim().includes('Medical Records')) {
                    console.log('Showing Medical Records section');
                    mainContent.style.display = 'none';
                    patientList.style.display = 'none';
                    settings.style.display = 'none';
                    medRecords.style.display = '';

                } else if (this.textContent.trim().includes('Home')) {
                    console.log('Showing Home section');
                    mainContent.style.display = '';
                    patientList.style.display = 'none';
                    medRecords.style.display = 'none';
                    settings.style.display = 'none';
                }
                else if (this.textContent.trim().includes('Settings'))
                  {
                    console.log('Settings opened');
                    settings.style.display = ''
                    mainContent.style.display = 'none';
                    patientList.style.display = 'none';
                    medRecords.style.display = 'none';
                  }
            });
        });

        document.querySelector('.sign-out').addEventListener('click', function() {
            if (confirm('Are you sure you want to sign out?')) {
                alert('Signing out...');
                // In a real application, this would redirect to login page
            }
        });

        // Calendar navigation with AJAX
        (function() {
            const calendarTitle = document.getElementById('calendar-title');
            const calendarContainer = document.getElementById('calendar-container');
            let currentMonth = <?php echo date('n'); ?>;
            let currentYear = <?php echo date('Y'); ?>;
            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];

            function updateCalendar(year, month) {
                fetch(`dashboard.php?calendar_ajax=1&year=${year}&month=${month}`)
                    .then(res => res.text())
                    .then(html => {
                        calendarContainer.innerHTML = html;
                        calendarTitle.textContent = `${monthNames[month-1]} ${year}`;
                        currentMonth = month;
                        currentYear = year;
                    });
            }

            document.getElementById('calendar-prev').addEventListener('click', function() {
                let month = currentMonth - 1;
                let year = currentYear;
                if (month < 1) {
                    month = 12;
                    year--;
                }
                updateCalendar(year, month);
            });
            document.getElementById('calendar-next').addEventListener('click', function() {
                let month = currentMonth + 1;
                let year = currentYear;
                if (month > 12) {
                    month = 1;
                    year++;
                }
                updateCalendar(year, month);
            });
        })();

        // Patient search filter function
        document.getElementById('patient-search').addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const table = document.getElementById('patient-table');
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const patientId = row.cells[0].textContent.toLowerCase();
                const patientName = row.cells[1].textContent.toLowerCase();
                if (patientId.includes(filter) || patientName.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Show current time in Home dashboard
        function updateCurrentTime() {
            const el = document.getElementById('current-time');
            if (el) {
                const now = new Date();
                el.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        }
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();

        // Medical Records tab logic
        const physicalExamTab = document.getElementById('physicalExamTab');
        const medicalHistoryTab = document.getElementById('medicalHistoryTab');
        const physicalExamContent = document.getElementById('physicalExamContent');
        const medicalHistoryContent = document.getElementById('medicalHistoryContent');
        if (physicalExamTab && medicalHistoryTab && physicalExamContent && medicalHistoryContent) {
            physicalExamTab.onclick = function() {
                physicalExamTab.classList.add('active');
                physicalExamTab.style.color = '#7c3aed';
                physicalExamTab.style.fontWeight = '600';
                physicalExamTab.style.borderBottom = '2px solid #7c3aed';
                medicalHistoryTab.classList.remove('active');
                medicalHistoryTab.style.color = '#a0aec0';
                medicalHistoryTab.style.fontWeight = '500';
                medicalHistoryTab.style.borderBottom = '2px solid transparent';
                physicalExamContent.style.display = '';
                medicalHistoryContent.style.display = 'none';
            };
            medicalHistoryTab.onclick = function() {
                medicalHistoryTab.classList.add('active');
                medicalHistoryTab.style.color = '#7c3aed';
                medicalHistoryTab.style.fontWeight = '600';
                medicalHistoryTab.style.borderBottom = '2px solid #7c3aed';
                physicalExamTab.classList.remove('active');
                physicalExamTab.style.color = '#a0aec0';
                physicalExamTab.style.fontWeight = '500';
                physicalExamTab.style.borderBottom = '2px solid transparent';
                physicalExamContent.style.display = 'none';
                medicalHistoryContent.style.display = '';
            };
        }



        // Enhanced input field interactions
        document.addEventListener('DOMContentLoaded', function() {
          // Add focus effects to all input fields in visit analytics form
          const visitInputs = document.querySelectorAll('#visitAnalyticsForm input[type="text"]');
          visitInputs.forEach(input => {
            input.addEventListener('focus', function() {
              this.style.borderColor = '#667eea';
              this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
              this.style.transform = 'translateY(-1px)';
            });
            
            input.addEventListener('blur', function() {
              this.style.borderColor = '#e2e8f0';
              this.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
              this.style.transform = 'translateY(0)';
            });
          });

          // Add focus effects to input fields in physical exam form
          const physicalInputs = document.querySelectorAll('#physicalExamForm input[type="text"]');
          physicalInputs.forEach(input => {
            input.addEventListener('focus', function() {
              this.style.borderColor = '#667eea';
              this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
              this.style.transform = 'translateY(-1px)';
            });
            
            input.addEventListener('blur', function() {
              this.style.borderColor = '#e2e8f0';
              this.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
              this.style.transform = 'translateY(0)';
            });
          });

          // Add custom scrollbar styling for both modal forms
          const visitModalForm = document.getElementById('visitAnalyticsForm');
          const physicalModalForm = document.getElementById('physicalExamForm');
          
          if (visitModalForm) {
            visitModalForm.style.scrollbarWidth = 'thin';
            visitModalForm.style.scrollbarColor = '#cbd5e1 #f1f5f9';
          }
          
          if (physicalModalForm) {
            physicalModalForm.style.scrollbarWidth = 'thin';
            physicalModalForm.style.scrollbarColor = '#cbd5e1 #f1f5f9';
          }
        });

        // Visit Analytics Modal logic
        document.getElementById('openVisitAnalyticsModal').onclick = function() {
          // Get the selected visit date from the Search Medical Records card
          const searchVisitDateInput = document.getElementById('searchVisitDate');
          const selectedVisitDateDisplay = document.getElementById('selectedVisitDateDisplay');
          const hiddenVisitDate = document.getElementById('hiddenVisitDate');
          
          if (searchVisitDateInput && searchVisitDateInput.value) {
            // Format the date for display (MM/DD/YYYY)
            const date = new Date(searchVisitDateInput.value);
            const formattedDate = `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}/${date.getFullYear()}`;
            
            selectedVisitDateDisplay.textContent = formattedDate;
            hiddenVisitDate.value = searchVisitDateInput.value;
          } else {
            selectedVisitDateDisplay.textContent = '--';
            hiddenVisitDate.value = '';
          }
          
          document.getElementById('visitAnalyticsModal').style.display = 'flex';
        };
        document.getElementById('closeVisitAnalyticsModal').onclick = function() {
          document.getElementById('visitAnalyticsModal').style.display = 'none';
        };
document.getElementById('visitAnalyticsForm').onsubmit = function(e) 
{
  e.preventDefault();

  const form = e.target;

  // ✅ Ensure visit_date is populated from display
  const selectedDate = document.getElementById('selectedVisitDateDisplay').textContent.trim();
  document.getElementById('hiddenVisitDate').value = selectedDate;

  // ✅ Collect form data
  const formData = new FormData(form);

  // ✅ Optional: log for debugging
  console.log('Submitting visit analytics data:', Object.fromEntries(formData.entries()));

  // ✅ Send data to PHP backend
  fetch('auth/action/submit_visit_analytics.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (!data.success) {
      throw new Error(data.message || 'Unknown error occurred.');
    }

    // ✅ Add the data to the Visit Analytics table (optional local update)
    const visitData = Object.fromEntries(formData.entries());
    addVisitAnalyticsToTable(visitData);

    // ✅ Show success message
    alert(data.message || 'Visit analytics saved successfully!');

    // ✅ Close the modal
    document.getElementById('visitAnalyticsModal').style.display = 'none';

    // ✅ Reset form and display
    form.reset();
    document.getElementById('selectedVisitDateDisplay').textContent = '--';
  })
  .catch(error => {
    console.error('Error submitting visit analytics:', error);
    alert('Failed to save visit analytics: ' + error.message);
  });
};
//VAW Risk Assessment Submission
document.getElementById('submit-vaw-btn').addEventListener('click', function () {
  const form = document.getElementById('vaw-risk-form');
  const submitBtn = this;
  const formData = new FormData(form);

  // Validate required hidden fields
  const visitDate = formData.get('visit_date');
  const patientId = formData.get('patient_id');
  const staffId = formData.get('staff_id');

  if (!visitDate || !/^\d{4}-\d{2}-\d{2}$/.test(visitDate)) {
    alert('⚠️ Visit date is missing or invalid.');
    return;
  }

  if (!patientId || isNaN(patientId)) {
    alert('⚠️ Patient ID is missing or invalid.');
    return;
  }

  if (!staffId || isNaN(staffId)) {
    alert('⚠️ Staff ID is missing or invalid.');
    return;
  }

  // Disable button and show loading state
  submitBtn.disabled = true;
  submitBtn.textContent = 'Saving...';
  console.log('Submitting VAW form with:');
for (const [key, value] of formData.entries()) {
  console.log(`${key}: ${value}`);
}

  fetch('auth/action/submit_vaw_risk_assessment.php', {
    method: 'POST',
    body: formData
  })
  .then(res => {
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  })
  .then(data => {
    submitBtn.disabled = false;
    submitBtn.textContent = '💾 Save VAW Assessment';

    if (data.success) {
      alert('✅ VAW Risk Assessment saved successfully.');
      form.reset(); // Optional: clear form
    } else {
      alert('⚠️ Submission failed. Please check your inputs or try again.');
      console.warn('Server response:', data);
    }
  })
  .catch(err => {
    submitBtn.disabled = false;
    submitBtn.textContent = '💾 Save VAW Assessment';
    alert('❌ Error submitting form.');
    console.error('Fetch error:', err);
  });
});

        // Physical Examination Add New Modal logic
        document.addEventListener('DOMContentLoaded', function() 
        {
          const physicalExamAddNewBtn = document.getElementById('physicalExamAddNewBtn');
          
          if (physicalExamAddNewBtn) {
            physicalExamAddNewBtn.onclick = function() {
              // Get the selected visit date from the Search Medical Records card
              const searchVisitDateInput = document.getElementById('searchVisitDate');
              const physicalExamVisitDateDisplay = document.getElementById('physicalExamVisitDateDisplay');
              const physicalExamHiddenVisitDate = document.getElementById('physicalExamHiddenVisitDate');
              const vawExamHiddenVisitDate = document.getElementById('vawExamHiddenVisitDate');
              const medicalHistoryHiddenVisitDate = document.getElementById('medicalHistoryHiddenVisitDate');
              const obstetricalHistoryHiddenDate = document.getElementById('obstetricalHistoryHiddenDate');

              if (searchVisitDateInput && searchVisitDateInput.value) {
                // Format the date for display (MM/DD/YYYY)
                const date = new Date(searchVisitDateInput.value);
                const formattedDate = `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}/${date.getFullYear()}`;
                
                physicalExamVisitDateDisplay.textContent = formattedDate;
                physicalExamHiddenVisitDate.value = searchVisitDateInput.value;
                vawExamHiddenVisitDate.value = searchVisitDateInput.value;
                obstetricalHistoryHiddenDate.value = searchVisitDateInput.value;
                medicalHistoryHiddenVisitDate.value = searchVisitDateInput.value;
              } else {
                physicalExamVisitDateDisplay.textContent = '--';
                physicalExamHiddenVisitDate.value = '';
                vawExamHiddenVisitDate.value = '';
                medicalHistoryHiddenVisitDate.value = '';
                obstetricalHistoryHiddenDate.value = '';
              }
              
              // Check if physical exam modal exists
              const physicalExamModal = document.getElementById('physicalExamModal');
              
              if (physicalExamModal) {
                physicalExamModal.style.display = 'flex';
              }
            };
          }
        });



        // Medical History Modal Tab Switching
        document.addEventListener('DOMContentLoaded', function() {
          const tabButtons = document.querySelectorAll('.medical-history-tab');
          const tabContents = document.querySelectorAll('.tab-content');

          tabButtons.forEach(button => {
            button.addEventListener('click', function() {
              const targetTab = this.getAttribute('data-tab');
              
              // Remove active class from all tabs and contents
              tabButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.style.color = '#64748b';
                btn.style.fontWeight = '500';
                btn.style.borderBottom = '3px solid transparent';
              });
              
              tabContents.forEach(content => {
                content.style.display = 'none';
              });
              
              // Add active class to clicked tab
              this.classList.add('active');
              this.style.color = '#667eea';
              this.style.fontWeight = '600';
              this.style.borderBottom = '3px solid #667eea';
              
              // Show corresponding content
              document.getElementById(targetTab + '-content').style.display = 'block';
            });
          });
        });

        // Patient Assessment Modal Tab Switching
        document.addEventListener('DOMContentLoaded', function() {
          const patientAssessmentTabButtons = document.querySelectorAll('.patient-assessment-tab');
          const patientAssessmentTabContents = document.querySelectorAll('.tab-content');

          patientAssessmentTabButtons.forEach(button => {
            button.addEventListener('click', function() {
              const targetTab = this.getAttribute('data-tab');
              
              // Remove active class from all tabs and contents
              patientAssessmentTabButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.style.color = '#64748b';
                btn.style.fontWeight = '500';
                btn.style.borderBottom = '3px solid transparent';
              });
              
              patientAssessmentTabContents.forEach(content => {
                content.style.display = 'none';
              });
              
              // Add active class to clicked tab
              this.classList.add('active');
              this.style.color = '#667eea';
              this.style.fontWeight = '600';
              this.style.borderBottom = '3px solid #667eea';
              
              // Show corresponding content
              document.getElementById(targetTab + '-content').style.display = 'block';
            });
          });
        });

        // Function to toggle notes input visibility
        function toggleNotesInput(checkbox, notesId) {
          const notesInput = document.getElementById(notesId);
          if (notesInput) {
            notesInput.style.display = checkbox.checked ? 'block' : 'none';
            if (!checkbox.checked) {
              notesInput.value = ''; // Clear notes when unchecking
            }
          }
        }

        // Save functions for each tab
        function savePhysicalExamination() {
          // Collect form data from physical examination tab
          const formData = new FormData(document.getElementById('physicalExamForm'));
          const physicalExamData = {};
          
          // Get all checkbox values
          const checkboxes = document.querySelectorAll('#physical-examination-content input[type="checkbox"]:checked');
          checkboxes.forEach(checkbox => {
            const name = checkbox.name;
            if (!physicalExamData[name]) {
              physicalExamData[name] = [];
            }
            physicalExamData[name].push(checkbox.value);
          });
          
          // Get other input values
          const uterineDepth = document.querySelector('#physical-examination-content input[name="uterine_depth"]');
          if (uterineDepth) {
            physicalExamData.uterine_depth = uterineDepth.value;
          }
          
          console.log('Physical Examination Data:', physicalExamData);
          alert('Physical examination data saved successfully!');
        }

        function saveMedicalHistory() {
          // Collect form data from medical history tab
          const medicalHistoryData = {};
          
          // Get all checkbox values
          const checkboxes = document.querySelectorAll('#medical-history-content input[type="checkbox"]:checked');
          checkboxes.forEach(checkbox => {
            const name = checkbox.name;
            if (!medicalHistoryData[name]) {
              medicalHistoryData[name] = [];
            }
            medicalHistoryData[name].push(checkbox.value);
          });
          
          // Get notes values
          const notesInputs = document.querySelectorAll('#medical-history-content input[type="text"]');
          notesInputs.forEach(input => {
            if (input.value.trim()) {
              medicalHistoryData[input.name] = input.value;
            }
          });
          
          console.log('Medical History Data:', medicalHistoryData);
          alert('Medical history data saved successfully!');
        }

        function saveObstetricalHistory() {
          // Collect form data from obstetrical history tab
          const obstetricalData = {};
          
          // Get number inputs
          const numberInputs = document.querySelectorAll('#obstetrical-history-content input[type="number"]');
          numberInputs.forEach(input => {
            if (input.value) {
              obstetricalData[input.name] = input.value;
            }
          });
          
          // Get date inputs
          const dateInputs = document.querySelectorAll('#obstetrical-history-content input[type="date"]');
          dateInputs.forEach(input => {
            if (input.value) {
              obstetricalData[input.name] = input.value;
            }
          });
          
          // Get text inputs
          const textInputs = document.querySelectorAll('#obstetrical-history-content input[type="text"]');
          textInputs.forEach(input => {
            if (input.value.trim()) {
              obstetricalData[input.name] = input.value;
            }
          });
          
          // Get select values
          const selectInputs = document.querySelectorAll('#obstetrical-history-content select');
          selectInputs.forEach(select => {
            if (select.value) {
              obstetricalData[select.name] = select.value;
            }
          });
          
          // Get checkbox values
          const checkboxes = document.querySelectorAll('#obstetrical-history-content input[type="checkbox"]:checked');
          checkboxes.forEach(checkbox => {
            const name = checkbox.name;
            if (!obstetricalData[name]) {
              obstetricalData[name] = [];
            }
            obstetricalData[name].push(checkbox.value);
          });
          
          console.log('Obstetrical History Data:', obstetricalData);
          alert('Obstetrical history data saved successfully!');
        }

        function saveVAWRisk() {
          // Collect form data from VAW risk tab
          const vawData = {};
          
          // Get all checkbox values
          const checkboxes = document.querySelectorAll('#vaw-risk-content input[type="checkbox"]:checked');
          checkboxes.forEach(checkbox => {
            const name = checkbox.name;
            if (!vawData[name]) {
              vawData[name] = [];
            }
            vawData[name].push(checkbox.value);
          });
          
          // Get notes values
          const notesInputs = document.querySelectorAll('#vaw-risk-content input[type="text"]');
          notesInputs.forEach(input => {
            if (input.value.trim()) {
              vawData[input.name] = input.value;
            }
          });
          
          console.log('VAW Risk Data:', vawData);
          alert('VAW risk assessment data saved successfully!');
        }

        // Function to populate summary cards with modal data
        function populateSummaryCards() {
            console.log('Populating summary cards with modal data...');
            
            // Physical Examination Summary
            const physicalData = collectPhysicalExaminationData();
            updateSummaryCard('summary-conjunctiva', physicalData.conjunctiva);
            updateSummaryCard('summary-neck', physicalData.neck);
            updateSummaryCard('summary-thorax', physicalData.thorax);
            updateSummaryCard('summary-abdomen', physicalData.abdomen);
            updateSummaryCard('summary-extremities', physicalData.extremities);
            
            // Breast Examination - Left and Right with intelligent display
            updateBreastSummary('summary-breast-left-mass', physicalData.breast_left, 'mass');
            updateBreastSummary('summary-breast-left-nipple', physicalData.breast_left, 'nipple');
            updateBreastSummary('summary-breast-left-skin', physicalData.breast_left, 'skin');
            updateBreastSummary('summary-breast-left-axillary', physicalData.breast_left, 'axillary');
            updateBreastSummary('summary-breast-right-mass', physicalData.breast_right, 'mass');
            updateBreastSummary('summary-breast-right-nipple', physicalData.breast_right, 'nipple');
            updateBreastSummary('summary-breast-right-skin', physicalData.breast_right, 'skin');
            updateBreastSummary('summary-breast-right-axillary', physicalData.breast_right, 'axillary');
            
            // Uterine Depth
            updateSummaryCard('summary-uterine-depth', physicalData.uterine_depth);
            
            // Pelvic Examination Summary
            updateSummaryCard('summary-perinium', physicalData.perinium);
            updateSummaryCard('summary-vagina', physicalData.vagina);
            updateSummaryCard('summary-adnexa', physicalData.adnexa);
            updateSummaryCard('summary-cervix', physicalData.cervix);
            updateSummaryCard('summary-uterus', physicalData.uterus);
            
            // Medical History Summary - Detailed Notes
            const medicalData = collectMedicalHistoryData();
            
            // HEENT Notes
            updateSummaryCard('summary-epilepsy-notes', medicalData.epilepsy_notes);
            updateSummaryCard('summary-headache-notes', medicalData.headache_notes);
            updateSummaryCard('summary-vision-notes', medicalData.vision_notes);
            updateSummaryCard('summary-conjunctivitis-notes', medicalData.conjunctivitis_notes);
            updateSummaryCard('summary-thyroid-notes', medicalData.thyroid_notes);
            
            // Chest/Heart Notes
            updateSummaryCard('summary-chest-pain-notes', medicalData.chest_pain_notes);
            updateSummaryCard('summary-shortness-breath-notes', medicalData.shortness_breath_notes);
            updateSummaryCard('summary-breast-mass-notes', medicalData.breast_mass_notes);
            updateSummaryCard('summary-nipple-discharge-notes', medicalData.nipple_discharge_notes);
            updateSummaryCard('summary-systolic-notes', medicalData.systolic_notes);
            updateSummaryCard('summary-diastolic-notes', medicalData.diastolic_notes);
            updateSummaryCard('summary-family-history-notes', medicalData.family_history_notes);
            
            // Abdomen Notes
            updateSummaryCard('summary-abdomen-mass-notes', medicalData.abdomen_mass_notes);
            updateSummaryCard('summary-gallbladder-notes', medicalData.gallbladder_notes);
            updateSummaryCard('summary-liver-notes', medicalData.liver_notes);
            
            // Genital Notes
            updateSummaryCard('summary-uterine-mass-notes', medicalData.uterine_mass_notes);
            updateSummaryCard('summary-vaginal-discharge-notes', medicalData.vaginal_discharge_notes);
            updateSummaryCard('summary-intermenstrual-bleeding-notes', medicalData.intermenstrual_bleeding_notes);
            updateSummaryCard('summary-postcoital-bleeding-notes', medicalData.postcoital_bleeding_notes);
            
            // Extremities Notes
            updateSummaryCard('summary-varicosities-notes', medicalData.varicosities_notes);
            updateSummaryCard('summary-leg-pain-notes', medicalData.leg_pain_notes);
            
            // Skin Notes
            updateSummaryCard('summary-yellowish-skin-notes', medicalData.yellowish_notes);
            
            // History Notes
            updateSummaryCard('summary-smoking-notes', medicalData.smoking_notes);
            updateSummaryCard('summary-allergies-notes', medicalData.allergies_notes);
            updateSummaryCard('summary-drug-intake-notes', medicalData.drug_intake_notes);
            updateSummaryCard('summary-std-notes', medicalData.std_notes);
            updateSummaryCard('summary-multiple-partners-notes', medicalData.multiple_partners_notes);
            updateSummaryCard('summary-bleeding-tendencies-notes', medicalData.bleeding_tendencies_notes);
            updateSummaryCard('summary-anemia-notes', medicalData.anemia_notes);
            updateSummaryCard('summary-diabetes-notes', medicalData.diabetes_notes);
            
            // STI Risks Notes
            updateSummaryCard('summary-sti-multiple-partners-notes', medicalData.sti_multiple_partners_notes);
            updateSummaryCard('summary-sti-women-discharge-notes', medicalData.sti_women_discharge_notes);
            updateSummaryCard('summary-sti-women-itching-notes', medicalData.sti_women_itching_notes);
            updateSummaryCard('summary-sti-women-pain-notes', medicalData.sti_women_pain_notes);
            updateSummaryCard('summary-sti-women-treated-notes', medicalData.sti_women_treated_notes);
            updateSummaryCard('summary-sti-men-sores-notes', medicalData.sti_men_sores_notes);
            updateSummaryCard('summary-sti-men-pus-notes', medicalData.sti_men_pus_notes);
            updateSummaryCard('summary-sti-men-swollen-notes', medicalData.sti_men_swollen_notes);
            
            // Obstetrical History Summary
            const obstetricalData = collectObstetricalHistoryData();
            updateSummaryCard('summary-full-term', obstetricalData.full_term);
            updateSummaryCard('summary-abortions', obstetricalData.abortions);
            updateSummaryCard('summary-premature', obstetricalData.premature);
            updateSummaryCard('summary-living-children', obstetricalData.living_children);
            updateSummaryCard('summary-last-delivery', obstetricalData.last_delivery_date);
            updateSummaryCard('summary-lmp', obstetricalData.past_menstrual_period);
            
            // VAW Risk Summary - Updated with proper field mapping
            const vawData = collectVAWRiskData();
            
            // VAW Risk Factors - Check if each condition is present
            updateVAWRiskSummary('summary-vaw-domestic-violence', vawData.vaw_risk, 'domestic_violence');
            updateVAWRiskSummary('summary-vaw-unpleasant-relationship', vawData.vaw_risk, 'unpleasant_relationship');
            updateVAWRiskSummary('summary-vaw-partner-disapproves-visit', vawData.vaw_risk, 'partner_disapproves_visit');
            updateVAWRiskSummary('summary-vaw-partner-disagrees-fp', vawData.vaw_risk, 'partner_disagrees_fp');
            
            // Referred To - Check if others is selected and show notes
            updateVAWRiskSummary('summary-vaw-others-specify', vawData.referred_to, 'others');
            
            console.log('Summary cards populated successfully!');
        }
        
        // Helper function to update individual summary card fields
        function updateSummaryCard(elementId, value) {
            const element = document.getElementById(elementId);
            if (element) {
                if (value && value.length > 0) {
                    if (Array.isArray(value)) {
                        element.textContent = value.join(', ');
                    } else {
                        element.textContent = value;
                    }
                } else {
                    element.textContent = '—';
                }
            }
        }

        // Helper function to update breast summary fields with intelligent display
        function updateBreastSummary(elementId, breastData, condition) {
            const element = document.getElementById(elementId);
            if (element) {
                if (breastData && breastData.length > 0) {
                    // Check if the specific condition is present
                    const hasCondition = breastData.includes(condition);
                    if (hasCondition) {
                        element.textContent = '✓ Present';
                        element.style.color = '#dc2626'; // Red color for positive findings
                        element.style.fontWeight = '600';
                    } else {
                        element.textContent = '—';
                        element.style.color = '#64748b';
                        element.style.fontWeight = '500';
                    }
                } else {
                    element.textContent = '—';
                    element.style.color = '#64748b';
                    element.style.fontWeight = '500';
                }
            }
        }

        // Helper function to update VAW Risk summary fields with intelligent display
function updateVAWRiskSummary(elementId, isRisk) {
    const element = document.getElementById(elementId);
    console.log('Updating VAW Risk summary:', elementId, 'with value:', isRisk);

    if (!element) {
        console.warn('Element not found:', elementId);
        return;
    }

    if (isRisk === 1 || isRisk === true) {
        element.textContent = '⚠️ Risk Identified';
        element.style.color = '#dc2626'; // Red
        element.style.fontWeight = '600';
        console.log('Updated element to show risk');
    } else {
        element.textContent = '—';
        element.style.color = '#64748b'; // Slate
        element.style.fontWeight = '500';
        console.log('Updated element to show normal');
    }
}
        // Function to collect Physical Examination data
        function collectPhysicalExaminationData() {
            const data = {};
            
            // Collect checkbox values
            const checkboxes = document.querySelectorAll('#physical-examination-content input[type="checkbox"]:checked');
            checkboxes.forEach(checkbox => {
                const name = checkbox.name.replace('[]', '');
                if (!data[name]) {
                    data[name] = [];
                }
                data[name].push(checkbox.value);
            });
            
            // Collect text input values
            const textInputs = document.querySelectorAll('#physical-examination-content input[type="text"]');
            textInputs.forEach(input => {
                if (input.value.trim()) {
                    data[input.name] = input.value;
                }
            });
            
            // Special handling for breast data - separate left and right
            data.breast_left = data.breast_left || [];
            data.breast_right = data.breast_right || [];
            
            return data;
        }
        
        // Function to collect Medical History data
        function collectMedicalHistoryData() {
            const data = {};
            
            // Collect checkbox values
            const checkboxes = document.querySelectorAll('#medical-history-content input[type="checkbox"]:checked');
            checkboxes.forEach(checkbox => {
                const name = checkbox.name.replace('[]', '');
                if (!data[name]) {
                    data[name] = [];
                }
                data[name].push(checkbox.value);
            });
            
            // Collect notes values for all specific conditions
            const notesInputs = document.querySelectorAll('#medical-history-content input[type="text"]');
            notesInputs.forEach(input => {
                if (input.value.trim()) {
                    const baseName = input.name.replace('_notes', '');
                    data[baseName + '_notes'] = input.value.trim();
                }
            });
            
            return data;
        }
        
        // Function to collect Obstetrical History data
        function collectObstetricalHistoryData() {
            const data = {};
            
            // Collect all input values
            const inputs = document.querySelectorAll('#obstetrical-history-content input, #obstetrical-history-content select');
            inputs.forEach(input => {
                if (input.value && input.value.trim()) {
                    data[input.name] = input.value;
                }
            });
            
            return data;
        }
        
        // Function to collect VAW Risk data
        function collectVAWRiskData() {
            const data = {};
            
            console.log('Collecting VAW Risk data...');
            
            // Collect checkbox values
            const checkboxes = document.querySelectorAll('#vaw-risk-content input[type="checkbox"]:checked');
            console.log('Found VAW Risk checkboxes:', checkboxes.length);
            checkboxes.forEach(checkbox => {
                const name = checkbox.name.replace('[]', '');
                if (!data[name]) {
                    data[name] = [];
                }
                data[name].push(checkbox.value);
                console.log('Added VAW Risk checkbox:', name, checkbox.value);
            });
            
            // Collect notes values for all specific conditions
            const notesInputs = document.querySelectorAll('#vaw-risk-content input[type="text"]');
            console.log('Found VAW Risk notes inputs:', notesInputs.length);
            notesInputs.forEach(input => {
                if (input.value.trim()) {
                    const baseName = input.name.replace('_notes', '');
                    data[baseName + '_notes'] = input.value.trim();
                    console.log('Added VAW Risk notes:', baseName + '_notes', input.value.trim());
                }
            });
            
            console.log('Final VAW Risk data:', data);
            return data;
        }

        // Combined save and update summary function
        function saveAndUpdateSummary() {
            console.log('Save and Update Summary triggered');
            
            // First, collect and save all data
            const allData = {
                physicalExamination: {},
                medicalHistory: {},
                obstetricalHistory: {},
                vawRisk: {}
            };
            
            // Physical Examination Data
            const physicalCheckboxes = document.querySelectorAll('#physical-examination-content input[type="checkbox"]:checked');
            physicalCheckboxes.forEach(checkbox => {
                const name = checkbox.name;
                if (!allData.physicalExamination[name]) {
                    allData.physicalExamination[name] = [];
                }
                allData.physicalExamination[name].push(checkbox.value);
            });
            
            const uterineDepth = document.querySelector('#physical-examination-content input[name="uterine_depth"]');
            if (uterineDepth) {
                allData.physicalExamination.uterine_depth = uterineDepth.value;
            }
            
            // Medical History Data
            const medicalCheckboxes = document.querySelectorAll('#medical-history-content input[type="checkbox"]:checked');
            medicalCheckboxes.forEach(checkbox => {
                const name = checkbox.name;
                if (!allData.medicalHistory[name]) {
                    allData.medicalHistory[name] = [];
                }
                allData.medicalHistory[name].push(checkbox.value);
            });
            
            const medicalNotes = document.querySelectorAll('#medical-history-content input[type="text"]');
            medicalNotes.forEach(input => {
                if (input.value.trim()) {
                    allData.medicalHistory[input.name] = input.value;
                }
            });
            
            // Obstetrical History Data
            const obstetricalInputs = document.querySelectorAll('#obstetrical-history-content input, #obstetrical-history-content select');
            obstetricalInputs.forEach(input => {
                if (input.value && input.value.trim()) {
                    allData.obstetricalHistory[input.name] = input.value;
                }
            });
            
            // VAW Risk Data
            const vawCheckboxes = document.querySelectorAll('#vaw-risk-content input[type="checkbox"]:checked');
            vawCheckboxes.forEach(checkbox => {
                const name = checkbox.name;
                if (!allData.vawRisk[name]) {
                    allData.vawRisk[name] = [];
                }
                allData.vawRisk[name].push(checkbox.value);
            });
            
            const vawNotes = document.querySelectorAll('#vaw-risk-content input[type="text"]');
            vawNotes.forEach(input => {
                if (input.value.trim()) {
                    allData.vawRisk[input.name] = input.value;
                }
            });
            
            console.log('All Data Combined:', allData);
            
            // Then, populate summary cards with the collected data
            populateSummaryCards();
            
            // Show success message
            alert('All data saved successfully and summary cards updated!');
            
            // Here you would typically send the data to your server via AJAX
            // For now, we'll just show the success message
        }

        // Combined save function for all tabs
        function saveAllData() {
          // Collect data from all tabs
          const allData = {
            physicalExamination: {},
            medicalHistory: {},
            obstetricalHistory: {},
            vawRisk: {}
          };
          
          // Physical Examination Data
          const physicalCheckboxes = document.querySelectorAll('#physical-examination-content input[type="checkbox"]:checked');
          physicalCheckboxes.forEach(checkbox => {
            const name = checkbox.name;
            if (!allData.physicalExamination[name]) {
              allData.physicalExamination[name] = [];
            }
            allData.physicalExamination[name].push(checkbox.value);
          });
          
          const uterineDepth = document.querySelector('#physical-examination-content input[name="uterine_depth"]');
          if (uterineDepth) {
            allData.physicalExamination.uterine_depth = uterineDepth.value;
          }
          
          // Medical History Data
          const medicalCheckboxes = document.querySelectorAll('#medical-history-content input[type="checkbox"]:checked');
          medicalCheckboxes.forEach(checkbox => {
            const name = checkbox.name;
            if (!allData.medicalHistory[name]) {
              allData.medicalHistory[name] = [];
            }
            allData.medicalHistory[name].push(checkbox.value);
          });
          
          const medicalNotes = document.querySelectorAll('#medical-history-content input[type="text"]');
          medicalNotes.forEach(input => {
            if (input.value.trim()) {
              allData.medicalHistory[input.name] = input.value;
            }
          });
          
          // Obstetrical History Data
          const obstetricalInputs = document.querySelectorAll('#obstetrical-history-content input, #obstetrical-history-content select');
          obstetricalInputs.forEach(input => {
            if (input.value && input.value.trim()) {
              allData.obstetricalHistory[input.name] = input.value;
            }
          });
          
          // VAW Risk Data
          const vawCheckboxes = document.querySelectorAll('#vaw-risk-content input[type="checkbox"]:checked');
          vawCheckboxes.forEach(checkbox => {
            const name = checkbox.name;
            if (!allData.vawRisk[name]) {
              allData.vawRisk[name] = [];
            }
            allData.vawRisk[name].push(checkbox.value);
          });
          
          const vawNotes = document.querySelectorAll('#vaw-risk-content input[type="text"]');
          vawNotes.forEach(input => {
            if (input.value.trim()) {
              allData.vawRisk[input.name] = input.value;
            }
          });
          
          console.log('All Data Combined:', allData);
          
          // Populate summary cards with the collected data
          populateSummaryCards();
          
          alert('All data saved successfully and summary cards updated!');
          
          // Here you would typically send the data to your server via AJAX
          // For now, we'll just show the success message
        }

        // Function to bring modal to top
        function bringModalToTop() {
          const modal = document.getElementById('physicalExamModal');
          if (modal) {
            // Scroll the modal to the top of the viewport
            modal.scrollIntoView({ 
              behavior: 'smooth', 
              block: 'start',
              inline: 'nearest'
            });
            
            // Add a subtle animation effect
            modal.style.transform = 'scale(1.02)';
            modal.style.transition = 'transform 0.2s ease';
            
            // Reset transform after animation
            setTimeout(() => {
              modal.style.transform = 'scale(1)';
            }, 200);
            
            console.log('Modal scrolled to top');
          }
        }

        




        // Safe element access with null checks
        function safeSetOnclick(elementId, handler) {
            const element = document.getElementById(elementId);
            if (element) {
                element.onclick = handler;
            }
        }

        // Set onclick handlers safely
        safeSetOnclick('closePhysicalExamModal', function() {
            document.getElementById('physicalExamModal').style.display = 'none';
        });
        
        // Prevent form submission to avoid unwanted alerts
        document.addEventListener('DOMContentLoaded', function() {
            const physicalExamForm = document.getElementById('physicalExamForm');
            if (physicalExamForm) {
                physicalExamForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const formData = new FormData(physicalExamForm);

                    fetch('auth/action/submit_physical_exam_record.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('📦 Response:', data); // Optional debug

                        if (data.success) {
                            alert('Record saved successfully. ID: ' + data.record_id);
                        } else {
                            alert('Submission failed: ' + (data.error || 'Unknown error'));
                        }
                    })

                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while submitting the form.');
                    });

                    return false;
                });
            }
        });


        safeSetOnclick('nextPhysicalExamModal', function() {
            document.getElementById('physicalExamPage1').style.display = 'none';
            document.getElementById('physicalExamPage2').style.display = '';
        });
        
        safeSetOnclick('backPhysicalExamModal', function() {
            document.getElementById('physicalExamPage1').style.display = '';
            document.getElementById('physicalExamPage2').style.display = 'none';
        });





        // Search Medical Records functionality
        function formatYMDToMDY(ymd) {
            const parts = ymd.split('-');
            if (parts.length !== 3) return ymd;
            return `${parts[1]}/${parts[2]}/${parts[0]}`;
        }

        // Function to collect Visit Analytics data from the form
        function collectVisitAnalyticsData() {
            const data = {};
            
            console.log('Collecting Visit Analytics data...');
            
            // Collect all input values from the visit analytics form
            const inputs = document.querySelectorAll('#visitAnalyticsForm input[type="text"], #visitAnalyticsForm input[type="hidden"]');
            inputs.forEach(input => {
                if (input.value && input.value.trim()) {
                    data[input.name] = input.value.trim();
                    console.log('Added Visit Analytics input:', input.name, input.value.trim());
                }
            });
            
            console.log('Final Visit Analytics data:', data);
            return data;
        }

        // Function to add visit analytics data to the table
        function addVisitAnalyticsToTable(visitData) {
            console.log('Adding visit analytics to table:', visitData);
            
            const tableBody = document.querySelector('#visitAnalyticsTable tbody');
            if (!tableBody) {
                console.error('Visit analytics table body not found');
                return;
            }
            
            // Format the visit date for display
            const visitDate = visitData.visit_date || new Date().toISOString().split('T')[0];
            const formattedDate = formatYMDToMDY(visitDate);
            
            // Create a new table row
            const newRow = document.createElement('tr');
            newRow.style.borderBottom = '1px solid #e2e8f0';
            
            // Add cells with the data
            newRow.innerHTML = `
                <td style="padding: 12px 10px; color: #1e293b; font-weight: 500;">${formattedDate}</td>
                <td style="padding: 12px 10px; color: #64748b;">${visitData.bp || '—'}</td>
                <td style="padding: 12px 10px; color: #64748b;">${visitData.temp || '—'}</td>
                <td style="padding: 12px 10px; color: #64748b;">${visitData.weight || '—'}</td>
                <td style="padding: 12px 10px; color: #64748b;">${visitData.fundal_height || '—'}</td>
                <td style="padding: 12px 10px; color: #64748b;">${visitData.fetal_heart_tone || '—'}</td>
                <td style="padding: 12px 10px; color: #64748b;">${visitData.fetal_position || '—'}</td>
                <td style="padding: 12px 10px; color: #64748b;">${visitData.chief_complaint || '—'}</td>
            `;
            
            // Add the new row to the table
            tableBody.appendChild(newRow);
            
            console.log('Visit analytics row added to table successfully');
        }

                // Function to populate visit analytics table
        function populateVisitAnalytics(selectedDate) {
            console.log('Populating visit analytics for date:', selectedDate);
            
            const tableBody = document.querySelector('#visitAnalyticsTable tbody');
            if (tableBody) {
                // Clear existing data
                tableBody.innerHTML = '';
                
                // In a real application, this would fetch data from a database
                // For now, we'll show a message that no data exists for this date
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="8" style="padding:8px 10px; text-align:center; color:#666;">
                        No visit analytics found for ${formatYMDToMDY(selectedDate)}
                    </td>
                `;
                tableBody.appendChild(row);
                console.log('Visit analytics table cleared for new date search');
            }
        }

        // Function to populate patient assessment summary cards
function populatePatientAssessment(selectedDate) {
  console.log('Populating patient assessment for date:', selectedDate);

  fetch(`/JAM_LYINGin/auth/action/staff/staff_get_patient_assessment.php?date=${encodeURIComponent(selectedDate)}`, {
    method: 'GET',
    credentials: 'same-origin'
  })
  .then(res => res.json())
  .then(response => {
    if (response.status === 'success' && response.data) {
      const exam = response.data.physicalExamination;
    if (!exam) {
      console.warn('[Empty] No physical examination found for selected date:', selectedDate);
    } else {
      console.log('[Reached] Physical examination fetched:', exam);
   // Conjunctiva
      let conjunctiva = [];
      if (exam.conjunctiva_pale) conjunctiva.push('Pale');
      if (exam.conjunctiva_yellowish) conjunctiva.push('Yellowish');
      document.getElementById('summary-conjunctiva').textContent = conjunctiva.length ? conjunctiva.join(', ') : 'Normal';

      // Neck
      let neck = [];
      if (exam.neck_enlarged_thyroid) neck.push('Enlarged Thyroid');
      if (exam.neck_enlarged_lymph_nodes) neck.push('Enlarged Lymph Nodes');
      document.getElementById('summary-neck').textContent = neck.length ? neck.join(', ') : 'Normal';

      // Thorax
      let thorax = [];
      if (exam.thorax_abnormal_heart_sounds) thorax.push('Abnormal Heart Sounds');
      if (exam.thorax_abnormal_breath_sounds) thorax.push('Abnormal Breath Sounds');
      document.getElementById('summary-thorax').textContent = thorax.length ? thorax.join(', ') : 'Clear';

      // Abdomen
      let abdomen = [];
      if (exam.abdomen_enlarged_liver) abdomen.push('Enlarged Liver');
      if (exam.abdomen_mass) abdomen.push('Mass');
      if (exam.abdomen_tenderness) abdomen.push('Tenderness');
      document.getElementById('summary-abdomen').textContent = abdomen.length ? abdomen.join(', ') : 'Soft';

      // Extremities
      let extremities = [];
      if (exam.extremities_edema) extremities.push('Edema');
      if (exam.extremities_varicosities) extremities.push('Varicosities');
      document.getElementById('summary-extremities').textContent = extremities.length ? extremities.join(', ') : 'Normal';

      // Breast Left
      document.getElementById('summary-breast-left-mass').textContent = exam.breast_left_mass ? 'Mass Present' : 'No Mass';
      document.getElementById('summary-breast-left-nipple').textContent = exam.breast_left_nipple_discharge ? 'Discharge' : 'Dry';
      document.getElementById('summary-breast-left-skin').textContent = exam.breast_left_skin_dimpling ? 'Skin Dimpling' : 'Normal Skin';
      document.getElementById('summary-breast-left-axillary').textContent = exam.breast_left_axillary_nodes ? 'Axillary Nodes Palpable' : 'No Nodes';

      // Breast Right
      document.getElementById('summary-breast-right-mass').textContent = exam.breast_right_mass ? 'Mass Present' : 'No Mass';
      document.getElementById('summary-breast-right-nipple').textContent = exam.breast_right_nipple_discharge ? 'Discharge' : 'Dry';
      document.getElementById('summary-breast-right-skin').textContent = exam.breast_right_skin_dimpling ? 'Skin Dimpling' : 'Normal Skin';
      document.getElementById('summary-breast-right-axillary').textContent = exam.breast_right_axillary_nodes ? 'Axillary Nodes Palpable' : 'No Nodes';

      // Perineum
      let perineum = [];
      if (exam.perineum_scars) perineum.push('Scars');
      if (exam.perineum_warts) perineum.push('Warts');
      if (exam.perineum_reddish) perineum.push('Reddish');
      if (exam.perineum_lacerations) perineum.push('Lacerations');
      document.getElementById('summary-perinium').textContent = perineum.length ? perineum.join(', ') : 'Normal';

      // Vagina
      let vagina = [];
      if (exam.vagina_congested) vagina.push('Congested');
      if (exam.vagina_bartholins_cyst) vagina.push("Bartholin's Cyst");
      if (exam.vagina_warts) vagina.push('Warts');
      if (exam.vagina_skenes_discharge) vagina.push("Skene's Discharge");
      if (exam.vagina_rectocele) vagina.push('Rectocele');
      if (exam.vagina_cystocele) vagina.push('Cystocele');
      document.getElementById('summary-vagina').textContent = vagina.length ? vagina.join(', ') : 'Normal';

      // Adnexa
      let adnexa = [];
      if (exam.adnexa_mass) adnexa.push('Mass');
      if (exam.adnexa_tenderness) adnexa.push('Tenderness');
      document.getElementById('summary-adnexa').textContent = adnexa.length ? adnexa.join(', ') : 'Normal';

      // Cervix
      let cervix = [];
      if (exam.cervix_congested) cervix.push('Congested');
      if (exam.cervix_erosion) cervix.push('Erosion');
      if (exam.cervix_consistency) cervix.push(`Consistency: ${exam.cervix_consistency}`);
      document.getElementById('summary-cervix').textContent = cervix.length ? cervix.join(', ') : 'Normal';

      // Uterus
      let uterus = [];
      if (exam.uterus_position) uterus.push(`Position: ${exam.uterus_position}`);
      if (exam.uterus_size) uterus.push(`Size: ${exam.uterus_size}`);
      document.getElementById('summary-uterus').textContent = uterus.length ? uterus.join(', ') : 'Normal';

      // Uterine Depth
      document.getElementById('summary-uterine-depth').textContent = exam.uterine_depth_cm ?? '—';
    }


   
      const vaw = response.data.vawRisk;
      if (!vaw) {
        console.warn('[Empty] No VAW assessment found for selected date:', selectedDate);
      } else {
        console.log('[Reached] VAW assessment fetched:', vaw);

        updateVAWRiskSummary('summary-vaw-domestic-violence', vaw.history_domestic_violence, 'domestic_violence');
        updateVAWRiskSummary('summary-vaw-unpleasant-relationship', vaw.unpleasant_relationship, 'unpleasant_relationship');
        updateVAWRiskSummary('summary-vaw-partner-disapproves-visit', vaw.partner_disapproves_visit, 'partner_disapproves_visit');
        updateVAWRiskSummary('summary-vaw-partner-disagrees-fp', vaw.partner_disagrees_fp, 'partner_disagrees_fp');
        
        // Referral - Others only
        updateVAWRiskSummary('summary-vaw-others-specify', vaw.referred_to_others, 'others');
      }

      
    } else {
      console.warn('No patient assessment data found for the selected date');
    }


  })
  .catch(err => {
    console.error('Error fetching patient assessment:', err);
  });
}
        // Helper function to update summary card fields
        function updateSummaryCardField(fieldName, value) {
            const fieldElement = document.querySelector(`[data-field="${fieldName}"]`);
            if (fieldElement) {
                fieldElement.textContent = value || '—';
            }
        }

        function bindSearchMedicalRecordsButtons() {
            const searchBtn = document.getElementById('searchVisitDateBtn');
            const addBtn = document.getElementById('addVisitDateBtn');
            const dateInput = document.getElementById('searchVisitDate');

            if (!searchBtn || !addBtn || !dateInput) {
                // Retry until DOM is ready
                setTimeout(bindSearchMedicalRecordsButtons, 100);
                return;
            }

            console.log('Binding search medical records buttons...');

            if (!searchBtn.dataset.bound) {
                searchBtn.addEventListener('click', function() {
                    console.log('Search Visit Date button clicked');
                    const selectedDate = dateInput.value;
                    
                    if (!selectedDate) {
                        alert('Please select a date first.');
                        return;
                    }
                    
                    const formattedDate = formatYMDToMDY(selectedDate);
                    console.log('Setting active visit date:', formattedDate);
                    
                    // Store the date for other parts of the application
                    window.activeVisitDate = selectedDate;
                    window.activeVisitDateMDY = formattedDate;
                    
                    // Visual feedback
                    searchBtn.style.background = 'linear-gradient(90deg, #059669 0%, #047857 100%)';
                    searchBtn.textContent = '✓ Searching...';
                    
                    // Automatically populate visit analytics and patient assessment data
                    populateVisitAnalytics(selectedDate);
                    populatePatientAssessment(selectedDate);
                    
                    setTimeout(() => {
                        searchBtn.style.background = 'linear-gradient(90deg, #667eea 0%, #764ba2 100%)';
                        searchBtn.textContent = 'Search Visit Date';
                    }, 2000);
                    
                    console.log(`Searching for medical records on ${formattedDate}`);
                });
                searchBtn.dataset.bound = 'true';
                console.log('Search button bound successfully');
            }

            if (!addBtn.dataset.bound) {
                addBtn.addEventListener('click', function() {
                    console.log('Add Visit Date button clicked');
                    const selectedDate = dateInput.value;
                    
                    if (!selectedDate) {
                        alert('Please select a date first.');
                        return;
                    }
                    
                    const formattedDate = formatYMDToMDY(selectedDate);
                    console.log('Setting active visit date:', formattedDate);
                    
                    // Store the date for other parts of the application
                    window.activeVisitDate = selectedDate;
                    window.activeVisitDateMDY = formattedDate;
                    
                    // Visual feedback
                    addBtn.style.background = 'linear-gradient(90deg, #059669 0%, #047857 100%)';
                    addBtn.textContent = '✓ Date Added';
                    
                    setTimeout(() => {
                        addBtn.style.background = 'linear-gradient(90deg, #f8f9fe 0%, #e0e7ff 100%)';
                        addBtn.textContent = 'Add Visit Date';
                    }, 2000);
                    
                    console.log(`Added new visit date: ${formattedDate}`);
                });
                addBtn.dataset.bound = 'true';
                console.log('Add button bound successfully');
            }

            // Mark as initialized
            console.log('Search medical records functionality initialized successfully');
        }

        window.initializeSearchMedicalRecords = function() {
            console.log('Initializing search medical records...');
            bindSearchMedicalRecordsButtons();
        };

        // Initialize Search Medical Records functionality on page load
        setTimeout(initializeSearchMedicalRecords, 100);
        
        // Also try to initialize immediately if DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeSearchMedicalRecords);
        } else {
            initializeSearchMedicalRecords();
        }

        // Age of Gestation Calculation Functionality
        function initializeAgeOfGestation() {
            const lmpDateInput = document.getElementById('lmpDate');
            const edcDateInput = document.getElementById('edcDate');
            const aogDisplay = document.querySelector('.age-of-gestation-display');
            
            if (lmpDateInput && edcDateInput) {
                console.log('Initializing Age of Gestation calculation...');
                
                // Add event listeners for LMP input - calculate AOG and EDC immediately
                lmpDateInput.addEventListener('change', function() {
                    console.log('LMP date changed:', this.value);
                    calculateAgeOfGestation();
                });
                
                lmpDateInput.addEventListener('input', function() {
                    if (this.value) {
                        console.log('LMP date input:', this.value);
                        calculateAgeOfGestation();
                    }
                });
                
                // Also calculate when EDC is manually entered
                edcDateInput.addEventListener('change', function() {
                    if (this.value) {
                        console.log('EDC date changed:', this.value);
                        calculateAgeOfGestationFromEDC();
                    }
                });
                
                edcDateInput.addEventListener('input', function() {
                    if (this.value) {
                        console.log('EDC date input:', this.value);
                        calculateAgeOfGestationFromEDC();
                    }
                });
                
                // If LMP already has a value, calculate immediately
                if (lmpDateInput.value) {
                    console.log('LMP already has value, calculating immediately:', lmpDateInput.value);
                    setTimeout(() => calculateAgeOfGestation(), 100);
                }
                
                console.log('Age of Gestation event listeners added successfully');
            } else {
                console.log('Age of Gestation elements not found, retrying...');
                setTimeout(initializeAgeOfGestation, 100);
            }
        }
        
        function calculateAgeOfGestation() {
            const lmpDateInput = document.getElementById('lmpDate');
            const edcDateInput = document.getElementById('edcDate');
            const aogDisplay = document.querySelector('.age-of-gestation-display');
            
            if (lmpDateInput && lmpDateInput.value) {
                console.log('Calculating AOG from LMP:', lmpDateInput.value);
                
                // Handle different date formats
                let lmpDate;
                if (lmpDateInput.value.includes('/')) {
                    // Handle MM/DD/YYYY format
                    const parts = lmpDateInput.value.split('/');
                    lmpDate = new Date(parts[2], parts[0] - 1, parts[1]);
                    console.log('Parsed MM/DD/YYYY date:', lmpDate);
                } else {
                    // Handle YYYY-MM-DD format (standard HTML date input)
                    lmpDate = new Date(lmpDateInput.value);
                    console.log('Parsed YYYY-MM-DD date:', lmpDate);
                }
                
                const today = new Date();
                console.log('Today:', today.toISOString().split('T')[0]);
                
                // Validate the date
                if (isNaN(lmpDate.getTime())) {
                    console.error('Invalid LMP date format');
                    return;
                }
                
                // Calculate EDC (LMP + 280 days or 40 weeks)
                const edcDate = new Date(lmpDate);
                edcDate.setDate(edcDate.getDate() + 280);
                
                // Calculate AOG in weeks and days
                const timeDiff = today.getTime() - lmpDate.getTime();
                const daysDiff = Math.floor(timeDiff / (1000 * 3600 * 24));
                const weeks = Math.floor(daysDiff / 7);
                const days = daysDiff % 7;
                
                console.log('Calculated AOG:', `${weeks}w ${days}d`);
                console.log('Calculated EDC:', edcDate.toISOString().split('T')[0]);
                
                // Update EDC input automatically
                if (edcDateInput) {
                    edcDateInput.value = edcDate.toISOString().split('T')[0];
                    console.log('EDC input updated automatically');
                }
                
                // Update AOG display
                if (aogDisplay) {
                    if (weeks >= 0) {
                        aogDisplay.textContent = `${weeks}w ${days}d`;
                        console.log('AOG display updated');
                    } else {
                        aogDisplay.textContent = '0w';
                        console.log('AOG display set to 0w (negative weeks)');
                    }
                }
                
                // Update circle progress
                updateCircleProgress(weeks);
                console.log('Circle progress updated');
            }
        }
        
        function calculateAgeOfGestationFromEDC() {
            const lmpDateInput = document.getElementById('lmpDate');
            const edcDateInput = document.getElementById('edcDate');
            const aogDisplay = document.querySelector('.age-of-gestation-display');
            
            if (edcDateInput && edcDateInput.value) {
                const edcDate = new Date(edcDateInput.value);
                const today = new Date();
                
                console.log('Calculating AOG from EDC:', edcDateInput.value);
                console.log('Today:', today.toISOString().split('T')[0]);
                
                // Calculate LMP (EDC - 280 days)
                const lmpDate = new Date(edcDate);
                lmpDate.setDate(lmpDate.getDate() - 280);
                
                // Calculate AOG in weeks and days
                const timeDiff = today.getTime() - lmpDate.getTime();
                const daysDiff = Math.floor(timeDiff / (1000 * 3600 * 24));
                const weeks = Math.floor(daysDiff / 7);
                const days = daysDiff % 7;
                
                console.log('Calculated AOG:', `${weeks}w ${days}d`);
                console.log('Calculated LMP:', lmpDate.toISOString().split('T')[0]);
                
                // Update LMP input automatically
                if (lmpDateInput) {
                    lmpDateInput.value = lmpDate.toISOString().split('T')[0];
                    console.log('LMP input updated automatically');
                }
                
                // Update AOG display
                if (aogDisplay) {
                    if (weeks >= 0) {
                        aogDisplay.textContent = `${weeks}w ${days}d`;
                        console.log('AOG display updated');
                    } else {
                        aogDisplay.textContent = '0w';
                        console.log('AOG display set to 0w (negative weeks)');
                    }
                }
                
                // Update circle progress
                updateCircleProgress(weeks);
                console.log('Circle progress updated');
            }
        }
        
        function updateCircleProgress(weeks) {
            // Calculate progress percentage (40 weeks = 100%)
            const maxWeeks = 40;
            const progress = Math.min((weeks / maxWeeks) * 100, 100);
            const strokeDashoffset = 251 - (251 * progress / 100);
            
            // Update the circle stroke
            const circles = document.querySelectorAll('svg circle[stroke="#6ee7b7"]');
            circles.forEach(circle => {
                circle.style.strokeDashoffset = strokeDashoffset;
            });
        }
        
        // Functions are now defined at the top of the script for global access

        // Initialize Age of Gestation functionality on page load
        setTimeout(initializeAgeOfGestation, 300);
        
        // Also initialize when Medical Records section is shown

    </script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('obstetricalHistoryForm');

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      e.stopPropagation();

      const formData = new FormData(form);

      fetch('auth/action/submit_obstetrical_history.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        console.log('📦 Response:', data);

        if (data.success) {
          alert('Obstetrical history saved. Record ID: ' + data.record_id);
        } else {
          alert('Submission failed: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the form.');
      });

      return false;
    });
  }
});
</script>
<script>
  //Loading of backend (php)
  document.addEventListener('DOMContentLoaded', function () {
    fetchMedicalHistoryData(); // or fetchMedicalHistoryData()
    fetchObstetricalHistoryData();
    fetchPhysicalExaminationData();
    fetchVawRiskAssessmentData();
    
});

function fetchMedicalHistoryData() {
    const patientId = <?= json_encode(PatientRecord::$id); ?>;

    fetch(`auth/action/staff/staff_get_latest_medical_history.php?patient_id=${encodeURIComponent(patientId)}`)
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success' && result.data) {
                const medicalData = result.data;
                console.log('Latest Medical History Data:', medicalData);
            // HEENT Notes
            updateSummaryCard('summary-epilepsy-notes', medicalData.epilepsy_notes);
            updateSummaryCard('summary-headache-notes', medicalData.headache_notes);
            updateSummaryCard('summary-vision-notes', medicalData.vision_notes);
            updateSummaryCard('summary-conjunctivitis-notes', medicalData.conjunctivitis_notes);
            updateSummaryCard('summary-thyroid-notes', medicalData.thyroid_notes);
            
            // Chest/Heart Notes
            updateSummaryCard('summary-chest-pain-notes', medicalData.chest_pain_notes);
            updateSummaryCard('summary-shortness-breath-notes', medicalData.shortness_breath_notes);
            updateSummaryCard('summary-breast-mass-notes', medicalData.breast_mass_notes);
            updateSummaryCard('summary-nipple-discharge-notes', medicalData.nipple_discharge_notes);
            updateSummaryCard('summary-systolic-notes', medicalData.systolic_notes);
            updateSummaryCard('summary-diastolic-notes', medicalData.diastolic_notes);
            updateSummaryCard('summary-family-history-notes', medicalData.family_history_notes);
            
            // Abdomen Notes
            updateSummaryCard('summary-abdomen-mass-notes', medicalData.abdomen_mass_notes);
            updateSummaryCard('summary-gallbladder-notes', medicalData.gallbladder_notes);
            updateSummaryCard('summary-liver-notes', medicalData.liver_notes);
            
            // Genital Notes
            updateSummaryCard('summary-uterine-mass-notes', medicalData.uterine_mass_notes);
            updateSummaryCard('summary-vaginal-discharge-notes', medicalData.vaginal_discharge_notes);
            updateSummaryCard('summary-intermenstrual-bleeding-notes', medicalData.intermenstrual_bleeding_notes);
            updateSummaryCard('summary-postcoital-bleeding-notes', medicalData.postcoital_bleeding_notes);
            
            // Extremities Notes
            updateSummaryCard('summary-varicosities-notes', medicalData.varicosities_notes);
            updateSummaryCard('summary-leg-pain-notes', medicalData.leg_pain_notes);
            
            // Skin Notes
            updateSummaryCard('summary-yellowish-skin-notes', medicalData.yellowish_notes);
            
            // History Notes
            updateSummaryCard('summary-smoking-notes', medicalData.smoking_notes);
            updateSummaryCard('summary-allergies-notes', medicalData.allergies_notes);
            updateSummaryCard('summary-drug-intake-notes', medicalData.drug_intake_notes);
            updateSummaryCard('summary-std-notes', medicalData.std_notes);
            updateSummaryCard('summary-multiple-partners-notes', medicalData.multiple_partners_notes);
            updateSummaryCard('summary-bleeding-tendencies-notes', medicalData.bleeding_tendencies_notes);
            updateSummaryCard('summary-anemia-notes', medicalData.anemia_notes);
            updateSummaryCard('summary-diabetes-notes', medicalData.diabetes_notes);
            
            // STI Risks Notes
            updateSummaryCard('summary-sti-multiple-partners-notes', medicalData.sti_multiple_partners_notes);
            updateSummaryCard('summary-sti-women-discharge-notes', medicalData.sti_women_discharge_notes);
            updateSummaryCard('summary-sti-women-itching-notes', medicalData.sti_women_itching_notes);
            updateSummaryCard('summary-sti-women-pain-notes', medicalData.sti_women_pain_notes);
            updateSummaryCard('summary-sti-women-treated-notes', medicalData.sti_women_treated_notes);
            updateSummaryCard('summary-sti-men-sores-notes', medicalData.sti_men_sores_notes);
            updateSummaryCard('summary-sti-men-pus-notes', medicalData.sti_men_pus_notes);
            updateSummaryCard('summary-sti-men-swollen-notes', medicalData.sti_men_swollen_notes);
            
            }
        })
        .catch(error => {
            console.error('Error fetching medical history:', error);
        });
}
function fetchObstetricalHistoryData() {
    const patientId = <?= json_encode(PatientRecord::$id); ?>;

    fetch(`auth/action/staff/staff_get_latest_obstetrical_history.php?patient_id=${encodeURIComponent(patientId)}`)
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success' && result.data) {
                const data = result.data;
                console.log('[Reached] Obstetrical history fetched:', data);
                
                
                // Example: update summary cards or form fields
                document.getElementById('summary-full-term').textContent = data.full_term ?? '—';
                document.getElementById('summary-abortions').textContent = data.abortions ?? '—';
                document.getElementById('summary-premature').textContent = data.premature ?? '—';
                document.getElementById('summary-living-children').textContent = data.living_children ?? '—';
                updateSummaryCard('summary-last-delivery', data.last_delivery_date);
                updateSummaryCard('summary-lmp', data.past_menstrual_period);
            
      

         
            } else {
                console.warn('[Empty] No obstetrical history found for patient:', patientId);
            }
        })
        .catch(error => {
            console.error('Error fetching obstetrical history:', error);
        });
}
function fetchPhysicalExaminationData() {
const patientId = <?= json_encode(PatientRecord::$id); ?>;

fetch(`auth/action/staff/staff_get_latest_physical_examination.php?patient_id=${patientId}`)
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success' && result.data) {
            const raw = result.data;
            console.log('[Reached] Physical examination fetched:', raw);

            // Conjunctiva
            let conjunctiva = [];
            if (raw.conjunctiva_pale) conjunctiva.push('Pale');
            if (raw.conjunctiva_yellowish) conjunctiva.push('Yellowish');
            document.getElementById('summary-conjunctiva').textContent = conjunctiva.length ? conjunctiva.join(', ') : 'Normal';

            // Neck
            let neck = [];
            if (raw.neck_enlarged_thyroid) neck.push('Enlarged Thyroid');
            if (raw.neck_enlarged_lymph_nodes) neck.push('Enlarged Lymph Nodes');
            document.getElementById('summary-neck').textContent = neck.length ? neck.join(', ') : 'Normal';

            // Thorax
            let thorax = [];
            if (raw.thorax_abnormal_heart_sounds) thorax.push('Abnormal Heart Sounds');
            if (raw.thorax_abnormal_breath_sounds) thorax.push('Abnormal Breath Sounds');
            document.getElementById('summary-thorax').textContent = thorax.length ? thorax.join(', ') : 'Clear';

            // Abdomen
            let abdomen = [];
            if (raw.abdomen_enlarged_liver) abdomen.push('Enlarged Liver');
            if (raw.abdomen_mass) abdomen.push('Mass');
            if (raw.abdomen_tenderness) abdomen.push('Tenderness');
            document.getElementById('summary-abdomen').textContent = abdomen.length ? abdomen.join(', ') : 'Soft';

            // Extremities
            let extremities = [];
            if (raw.extremities_edema) extremities.push('Edema');
            if (raw.extremities_varicosities) extremities.push('Varicosities');
            document.getElementById('summary-extremities').textContent = extremities.length ? extremities.join(', ') : 'Normal';

            // Breast Left
            document.getElementById('summary-breast-left-mass').textContent =
                raw.breast_left_mass ? 'Mass Present' : 'No Mass';

            document.getElementById('summary-breast-left-nipple').textContent =
                raw.breast_left_nipple_discharge ? 'Discharge' : 'Dry';

            document.getElementById('summary-breast-left-skin').textContent =
                raw.breast_left_skin_dimpling ? 'Skin Dimpling' : 'Normal Skin';

            document.getElementById('summary-breast-left-axillary').textContent =
                raw.breast_left_axillary_nodes ? 'Axillary Nodes Palpable' : 'No Nodes';

            // Breast Right
            document.getElementById('summary-breast-right-mass').textContent =
                raw.breast_right_mass ? 'Mass Present' : 'No Mass';

            document.getElementById('summary-breast-right-nipple').textContent =
                raw.breast_right_nipple_discharge ? 'Discharge' : 'Dry';

            document.getElementById('summary-breast-right-skin').textContent =
                raw.breast_right_skin_dimpling ? 'Skin Dimpling' : 'Normal Skin';

            document.getElementById('summary-breast-right-axillary').textContent =
                raw.breast_right_axillary_nodes ? 'Axillary Nodes Palpable' : 'No Nodes';

            // Perineum
            let perineum = [];
            if (raw.perineum_scars) perineum.push('Scars');
            if (raw.perineum_warts) perineum.push('Warts');
            if (raw.perineum_reddish) perineum.push('Reddish');
            if (raw.perineum_lacerations) perineum.push('Lacerations');
            document.getElementById('summary-perinium').textContent = perineum.length ? perineum.join(', ') : 'Normal';

            // Vagina
            let vagina = [];
            if (raw.vagina_congested) vagina.push('Congested');
            if (raw.vagina_bartholins_cyst) vagina.push("Bartholin's Cyst");
            if (raw.vagina_warts) vagina.push('Warts');
            if (raw.vagina_skenes_discharge) vagina.push("Skene's Discharge");
            if (raw.vagina_rectocele) vagina.push('Rectocele');
            if (raw.vagina_cystocele) vagina.push('Cystocele');
            document.getElementById('summary-vagina').textContent = vagina.length ? vagina.join(', ') : 'Normal';

            // Adnexa
            let adnexa = [];
            if (raw.adnexa_mass) adnexa.push('Mass');
            if (raw.adnexa_tenderness) adnexa.push('Tenderness');
            document.getElementById('summary-adnexa').textContent = adnexa.length ? adnexa.join(', ') : 'Normal';

            // Cervix
            let cervix = [];
            if (raw.cervix_congested) cervix.push('Congested');
            if (raw.cervix_erosion) cervix.push('Erosion');
            if (raw.cervix_consistency) cervix.push(`Consistency: ${raw.cervix_consistency}`);
            document.getElementById('summary-cervix').textContent = cervix.length ? cervix.join(', ') : 'Normal';

            // Uterus
            let uterus = [];
            if (raw.uterus_position) uterus.push(`Position: ${raw.uterus_position}`);
            if (raw.uterus_size) uterus.push(`Size: ${raw.uterus_size}`);
            document.getElementById('summary-uterus').textContent = uterus.length ? uterus.join(', ') : 'Normal';

            // Uterine Depth
            document.getElementById('summary-uterine-depth').textContent = raw.uterine_depth_cm ?? '—';
        } else {
            console.warn('[Empty] No physical examination found for patient:', patientId);
        }
    })
    .catch(error => {
        console.error('Error fetching physical examination:', error);
    });
}
function fetchVawRiskAssessmentData() {
    const patientId = <?= json_encode(PatientRecord::$id); ?>;

    fetch(`auth/action/staff/staff_get_vaw_risk_assessment.php?patient_id=${encodeURIComponent(patientId)}`)
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success' && result.data) {
                const data = result.data;
                console.log('[Reached] VAW risk assessment fetched:', data);
                console.log('[Reached] VAW risk assessment fetched:', data.history_domestic_violence);

                // VAW Risk Factors
                updateVAWRiskSummary('summary-vaw-domestic-violence', data.history_domestic_violence, 'domestic_violence');
                updateVAWRiskSummary('summary-vaw-unpleasant-relationship', data.unpleasant_relationship, 'unpleasant_relationship');
                updateVAWRiskSummary('summary-vaw-partner-disapproves-visit', data.partner_disapproves_visit, 'partner_disapproves_visit');
                updateVAWRiskSummary('summary-vaw-partner-disagrees-fp', data.partner_disagrees_fp, 'partner_disagrees_fp');

                // Referral - Others only
                updateVAWRiskSummary('summary-vaw-others-specify', data.referred_to_others, 'others');
            } else {
                console.warn('[Empty] No VAW risk assessment found for patient:', patientId);
            }
        })
        .catch(error => {
            console.error('Error fetching VAW risk assessment:', error);
        });
}
</script>

</body>
</html>
