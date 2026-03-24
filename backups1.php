<?php

session_start();
include_once 'config/config.php'; // Ensure $pdo is available
include_once 'config/roleGate.php';
requireRole(['clerk']);
if (!isset($_SESSION['clerk_name'])) {
    $_SESSION['clerk_name'] = 'Clerk Gregorio';
    
}
try {
    $stmt = $pdo->prepare("
        SELECT 
            ar.id,
            ar.patient_id,
            CONCAT(u_patient.first_name, ' ', u_patient.last_name) AS patient_name,
            ar.appointment_date,
            ar.chief_complaint,
            ar.status,
            CONCAT('Dr. ', u_doctor.first_name, ' ', u_doctor.last_name) AS doctor_name
        FROM appointment_requests ar
        JOIN users u_patient ON ar.patient_id = u_patient.id
        LEFT JOIN users u_doctor ON ar.doctor_id = u_doctor.id
        ORDER BY ar.appointment_date ASC, ar.appointment_time ASC
    ");
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
    exit;
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JAM Lying-in Clinic - Dashboard</title>
    <link rel="stylesheet" href="clerkdash.css">
    <!--
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #764ba2 0%, #ff6bcb 100%);
            backdrop-filter: blur(10px);
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
        }

        .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            letter-spacing: -0.5px;
        }

        .clerk-info {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .clerk-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }

        .clerk-name {
            color: white;
            font-weight: 500;
        }

        .nav-menu {
            flex: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.25);
        }

        .nav-icon {
            font-size: 18px;
        }

        .sign-out {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 12px 15px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .sign-out:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .main-content {
            flex: 1;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(108, 51, 110, 0.9);
            border-radius: 20px;
            padding: 25px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .card-icon {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .card-subtitle {
            opacity: 0.8;
            font-size: 14px;
        }

        .content-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px 30px 30px 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            box-sizing: border-box;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            align-items: start;
        }
        
        .appointments-section {
            grid-column: 1;
            margin-bottom: 20px;
        }
        
        .appointments-section:last-of-type {
            margin-bottom: 0;
        }
        
        .calendar-section {
            grid-column: 2;
            grid-row: 1 / span 3;
        }

        .calendar-section {
            padding-right: 0;
            margin-left: 0;
        }

        .calendar-section h2 {
            color: #6c336e;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-title {
            color: #6c336e;
            font-size: 18px;
            font-weight: 600;
        }

        .calendar-nav {
            display: flex;
            gap: 10px;
        }

        .nav-btn {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 5px;
            color: #6c336e;
        }

        .calendar {
            width: 100%;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }

        .calendar-day-header {
            text-align: center;
            font-weight: 600;
            color: #6c336e;
            padding: 10px 0;
            font-size: 14px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .calendar-day:hover {
            background: rgba(108, 51, 110, 0.1);
        }

        .calendar-day.today {
            background: #6c336e;
            color: white;
        }

        .calendar-day.empty {
            cursor: default;
        }

        /* Approve Appointment Card Styles */
        .approve-appointment-card {
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        }

        .approve-appointment-card h4 {
            color: #6c336e;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }

        .pending-appointment {
            background: #f8f9fe;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            border-left: 4px solid #ff6bcb;
            transition: all 0.2s ease;
        }

        .pending-appointment:last-child {
            margin-bottom: 0;
        }

        .pending-appointment:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .btn-approve {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-approve:hover {
            background: linear-gradient(90deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }



        .btn-edit {
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-edit:hover {
            background: linear-gradient(90deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        .summary-section {
            background: rgba(108, 51, 110, 0.05);
            border-radius: 15px;
            padding: 20px 15px;
            min-width: 220px;
            max-width: 300px;
            box-sizing: border-box;
        }

        .summary-title {
            color: #6c336e;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .summary-item {
            margin-bottom: 20px;
        }

        .summary-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 36px;
            font-weight: bold;
            color: #6c336e;
        }

        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
            
            .appointments-section {
                grid-column: 1;
                margin-bottom: 20px;
            }
            
            .appointments-section:last-of-type {
                margin-bottom: 0;
            }
            
            .calendar-section {
                grid-column: 2;
                grid-row: 1 / span 3;
            }
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 15px;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .appointments-section,
            .calendar-section {
                grid-column: 1;
                grid-row: auto;
            }
            
            .appointments-section {
                margin-bottom: 20px;
            }
            
            .appointments-section:last-of-type {
                margin-bottom: 0;
            }
            
            .content-section {
                padding: 20px;
            }
            
            .main-content {
                padding: 15px;
            }
        }

        .btn, .btn-primary {
            background: linear-gradient(90deg, #764ba2 0%, #ff6bcb 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 22px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(118,75,162,0.08);
            transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
        }
        .btn:hover, .btn-primary:hover {
            background: linear-gradient(90deg, #ff6bcb 0%, #764ba2 100%);
            box-shadow: 0 4px 16px rgba(118,75,162,0.15);
            transform: translateY(-2px) scale(1.03);
        }
        .btn-secondary {
            background: linear-gradient(90deg, #f8fafc 0%, #e0e7ff 100%);
            color: #7c3aed;
            border: none;
            border-radius: 8px;
            padding: 10px 22px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(124,58,237,0.08);
            transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
        }
        .btn-secondary:hover {
            background: linear-gradient(90deg, #e0e7ff 0%, #f8fafc 100%);
            box-shadow: 0 4px 16px rgba(124,58,237,0.12);
            transform: translateY(-2px) scale(1.03);
        }

        /* Modal Styles - Clean and Centered */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .modal-overlay.show {
            display: flex !important;
        }

        .modal-content {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            max-height: 80vh;
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* Modal Header */
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 24px 32px 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 20px 20px 0 0;
        }

        .modal-header h2,
        .modal-header h3 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: scale(1.1);
        }

        /* Modal Form */
        .modal-form {
            padding: 32px;
            background: #ffffff;
            flex: 1;
            overflow-y: auto;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 24px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            color: #1e293b;
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .form-group label::after {
            content: ' *';
            color: #ef4444;
            font-weight: 700;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            background: #ffffff;
            transition: all 0.3s ease;
            color: #1f2937;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
            line-height: 1.6;
        }

        .form-group select {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 16px center;
            background-repeat: no-repeat;
            background-size: 20px;
            padding-right: 48px;
            appearance: none;
        }

        /* Form Row */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        @media (max-width: 1200px) {
            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        /* Service Entry */
        .service-entry {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            position: relative;
        }

        .service-entry::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px 16px 0 0;
        }

        .service-entry:hover {
            border-color: #667eea;
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }

        /* Add More Container */
        .add-more-container {
            text-align: center;
            margin: 32px 0;
            position: relative;
        }

        .add-more-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, #e2e8f0 20%, #e2e8f0 80%, transparent 100%);
            z-index: 1;
        }

        .btn-add-more {
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: #ffffff;
            padding: 18px 36px;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        }

        .btn-add-more:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
        }

        .btn-add-more span {
            font-size: 22px;
            font-weight: 700;
        }

        /* Modal Actions */
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            padding: 24px 32px;
            background: #ffffff;
            border-top: 2px solid #f1f5f9;
            margin: 0 -32px -32px -32px;
        }

        @media (max-width: 768px) {
            .modal-actions {
                flex-direction: column;
                padding: 20px 24px;
                margin: 0 -24px -24px -24px;
            }
        }

        /* Action Buttons */
        .btn-cancel,
        .btn-save,
        .btn-cancel-modal,
        .btn-save-modal {
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-cancel,
        .btn-cancel-modal {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            color: #64748b;
        }

        .btn-cancel:hover,
        .btn-cancel-modal:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #475569;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-save,
        .btn-save-modal {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: #ffffff;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        }

        .btn-save:hover,
        .btn-save-modal:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modal-overlay {
                padding: 16px;
            }
            
            .modal-content {
                max-width: 100%;
                max-height: calc(100vh - 32px);
            }

            .modal-header {
                padding: 20px 24px 16px 24px;
            }

            .modal-header h2,
            .modal-header h3 {
                font-size: 20px;
            }

            .modal-form {
                padding: 24px;
            }

            .modal-actions {
                padding: 20px 24px;
            }

            .btn-cancel,
            .btn-save,
            .btn-cancel-modal,
            .btn-save-modal {
                width: 100%;
                justify-content: center;
                min-width: auto;
            }
        }

        @media (max-width: 480px) {
            .modal-overlay {
                padding: 12px;
            }
            
            .modal-content {
                max-height: calc(100vh - 24px);
            }
            
            .modal-header {
                padding: 16px 20px 12px 20px;
            }
            
            .modal-form {
                padding: 20px;
            }
            
            .modal-actions {
                padding: 16px 20px;
            }
        }



        /* Remove Service Button */
        .remove-service {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 2px solid #fecaca;
            color: #dc2626;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
        }

        .remove-service:hover {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #fca5a5;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(220, 38, 38, 0.2);
        }

        /* ===== HOSPITAL-STYLE MODAL CSS ===== */
        
        /* Hospital Modal Container */
        .hospital-modal {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.1);
            max-width: min(95vw, 900px);
            max-height: min(95vh, 800px);
        }

        /* Hospital Header */
        .hospital-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 20px 20px 0 0;
            padding: 28px 32px 24px 32px;
            position: relative;
            overflow: hidden;
        }

        .hospital-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            z-index: 2;
        }

        .header-icon {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header-icon svg {
            color: white;
        }

        .header-text h2 {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
        }

        .header-text p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin: 0;
            font-weight: 400;
        }

        .hospital-close {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            position: absolute;
            top: 24px;
            right: 24px;
        }

        .hospital-close:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.1);
        }

        /* Hospital Form */
        .hospital-form {
            padding: 32px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            position: relative;
            z-index: 1;
        }

        /* Service Entry Styling */
        .hospital-service-entry {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 32px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: visible;
            z-index: 1;
        }



        .hospital-service-entry:hover {
            border-color: #3b82f6;
            box-shadow: 0 8px 32px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }



        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-bottom: 32px;
            position: relative;
            z-index: 1;
        }

        @media (max-width: 1200px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 28px;
            }
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
        }

        /* Hospital Form Groups */
        .hospital-form-group {
            position: relative;
            margin-bottom: 8px;
            z-index: 1;
        }

        .hospital-form-group.full-width {
            grid-column: 1 / -1;
            margin-top: 16px;
        }

        .hospital-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            letter-spacing: 0.3px;
            position: relative;
            z-index: 2;
            min-height: 24px;
            flex-wrap: wrap;
            width: 100%;
        }

        .label-icon {
            font-size: 16px;
        }

        .required-mark {
            color: #ef4444;
            font-weight: 700;
            margin-left: 4px;
            position: relative;
            z-index: 3;
            display: inline-block;
        }

        /* Input Wrapper */
        .input-wrapper {
            position: relative;
            z-index: 1;
            margin-bottom: 8px;
        }

        .hospital-input,
        .hospital-select,
        .hospital-textarea {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            color: #1e293b;
            background: #ffffff;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .hospital-input:focus,
        .hospital-select:focus,
        .hospital-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
            z-index: 3;
            position: relative;
        }

        .hospital-input:hover,
        .hospital-select:hover,
        .hospital-textarea:hover {
            border-color: #cbd5e1;
        }

        .hospital-textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

        /* Select Styling */
        .hospital-select {
            appearance: none;
            cursor: pointer;
        }

        .select-arrow {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6b7280;
        }

        /* Input Border Animation */
        .input-border {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
            transition: width 0.3s ease;
        }

        .hospital-input:focus + .input-border,
        .hospital-textarea:focus + .input-border {
            width: 100%;
        }

        /* Add More Button */
        .hospital-add-more {
            text-align: center;
            margin: 32px 0;
            position: relative;
        }

        .hospital-add-more::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, #e2e8f0 20%, #e2e8f0 80%, transparent 100%);
            z-index: 1;
        }

        .hospital-btn-add {
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            padding: 20px 40px;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
        }

        .hospital-btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(59, 130, 246, 0.4);
        }

        .btn-icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Service Actions */
        .service-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        .hospital-remove-btn {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 2px solid #fecaca;
            color: #dc2626;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .hospital-remove-btn:hover {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #fca5a5;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(220, 38, 38, 0.2);
        }

        /* Hospital Action Buttons */
        .hospital-actions {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-top: 2px solid #e2e8f0;
            margin: 0 -32px -32px -32px;
            padding: 24px 32px;
            display: flex;
            justify-content: flex-end;
            gap: 16px;
        }

        .hospital-btn-cancel {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 2px solid #d1d5db;
            color: #6b7280;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 140px;
        }

        .hospital-btn-cancel:hover {
            background: linear-gradient(135deg, #f1f5f9 0%, #d1d5db 100%);
            border-color: #9ca3af;
            color: #374151;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .hospital-btn-save {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: #ffffff;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 180px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        }

        .hospital-btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(16, 185, 129, 0.4);
        }

        /* Responsive Hospital Modal */
        @media (max-width: 768px) {
            .hospital-modal {
                max-width: 95vw;
                max-height: 95vh;
            }
            
            .hospital-header {
                padding: 24px 20px 20px 20px;
            }
            
            .header-text h2 {
                font-size: 24px;
            }
            
            .header-text p {
                font-size: 14px;
            }
            
            .hospital-form {
                padding: 24px 20px;
            }
            
            .hospital-service-entry {
                padding: 20px;
            }
            
            .form-grid {
                gap: 20px;
            }
            
            .hospital-actions {
                flex-direction: column;
                padding: 20px;
                margin: 0 -20px -20px -20px;
            }
            
            .hospital-btn-cancel,
            .hospital-btn-save {
                width: 100%;
                justify-content: center;
            }
        }
    -->
</head>
<body>
    <div class="sidebar">
        <div class="logo">JAM Lying-in Clinic</div>
        
        <div class="clerk-info">
            <div class="clerk-avatar">👨‍💼</div>
            <div class="clerk-name"><?php echo $_SESSION['clerk_name']; ?></div>
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

            <a href="#" class="nav-item">
                <span class="nav-icon">💰</span>
                Services
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">💰</span>
                Billing
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
            <h2 style="margin-bottom: 28px; color: #222;">Welcome Clerk Gregorio</h2>
            <div class="dashboard-grid">
              <?php
$grouped = [];

// Filter and group only approved appointments
foreach ($appointments as $appt) {
    if (strtolower($appt['status']) !== 'approved') {
        continue; // Skip non-approved
    }

    $doctor = $appt['doctor_name'] ?? 'Unassigned';
    if (!isset($grouped[$doctor])) {
        $grouped[$doctor] = [];
    }
    $grouped[$doctor][] = $appt;
}

// Render each doctor's section
foreach ($grouped as $doctorName => $doctorAppointments):
?>
<div id="<?php echo strtolower(str_replace(' ', '-', $doctorName)); ?>-appointments" class="appointments-section" style="margin-top: 0; background: rgba(255,255,255,0.95); border-radius: 15px; padding: 25px; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
    <h3 style="color: #6c336e; margin-bottom: 18px;"><?php echo htmlspecialchars($doctorName); ?> Appointments</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f3e6f7; color: #6c336e;">
                <th style="padding: 10px 8px; text-align: left; border-radius: 8px 0 0 8px;">Patient ID</th>
                <th style="padding: 10px 8px; text-align: left;">Patient Name</th>
                <th style="padding: 10px 8px; text-align: left;">Date</th>
                <th style="padding: 10px 8px; text-align: left;">Concern</th>
                <th style="padding: 10px 8px; text-align: left; border-radius: 0 8px 8px 0;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($doctorAppointments) === 0): ?>
                <tr>
                    <td colspan="5" style="padding: 12px; text-align: center; color: #888;">
                        No approved appointments today.
                    </td>
                </tr>
            <?php else:
                foreach ($doctorAppointments as $appt): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($appt['patient_id']); ?></td>
                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($appt['patient_name']); ?></td>
                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($appt['appointment_date']); ?></td>
                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($appt['chief_complaint']); ?></td>
                        <td style="padding: 10px 8px;">
                            <select name="status[]" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #ccc;" disabled>
                                <option value="Approved" selected>Approved</option>
                            </select>
                        </td>
                    </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>

                
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
                     
                     <!-- Approve Appointment Card -->
                     <div class="approve-appointment-card" style="margin-top: 20px; background: rgba(255, 255, 255, 0.95); border-radius: 15px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
                         <h4 style="color: #6c336e; margin-bottom: 15px; font-size: 16px; font-weight: 600;">Approve Appointments</h4>
                         <div id="pending-appointments-container" style="max-height: 200px; overflow-y: auto;">
                             <!-- Pending appointments will be populated from backend -->
                             <div style="padding: 20px; text-align: center; color: #6b7280;">
                                 <div style="font-size: 16px; margin-bottom: 8px;">📅</div>
                                 <p style="margin: 0; font-size: 14px;">No pending appointments found. Data will be loaded from backend.</p>
                             </div>
                         </div>
                     </div>
                        <script>
                        const getPendingAppointmentsURL = 'http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_get_pending_appointments.php';
                        const approveAppointmentURL = 'http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_approve_appointments.php';

                        document.addEventListener('DOMContentLoaded', () => {
                        const container = document.getElementById('pending-appointments-container');

                        function loadPendingAppointments() {
                            fetch(getPendingAppointmentsURL)
                            .then(response => response.json())
                            .then(data => {
                                container.innerHTML = '';

                                if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
                                data.data.forEach(appt => {
                                    const card = document.createElement('div');
                                    card.style.padding = '12px 16px';
                                    card.style.borderBottom = '1px solid #eee';
                                    card.style.display = 'flex';
                                    card.style.justifyContent = 'space-between';
                                    card.style.alignItems = 'center';

                                    const info = document.createElement('div');
                                    info.innerHTML = `
                                    <strong>${appt.patient_name}</strong><br>
                                    <span style="font-size: 13px;">${appt.appointment_date} — ${appt.chief_complaint}</span>
                                    `;

                                    const approveBtn = document.createElement('button');
                                    approveBtn.textContent = 'Approve';
                                    approveBtn.style.padding = '6px 12px';
                                    approveBtn.style.borderRadius = '6px';
                                    approveBtn.style.backgroundColor = '#6c336e';
                                    approveBtn.style.color = 'white';
                                    approveBtn.style.border = 'none';
                                    approveBtn.style.cursor = 'pointer';

                                    approveBtn.addEventListener('click', () => {
                                    fetch(approveAppointmentURL, {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                        body: new URLSearchParams({ appointment_id: appt.appointment_id })
                                    })
                                    .then(res => res.json())
                                    .then(result => {
                                        if (result.status === 'success') {
                                        alert('Appointment approved');
                                        loadPendingAppointments(); // Refresh
                                        } else {
                                        alert('Failed to approve: ' + result.message);
                                        }
                                    })
                                    .catch(err => {
                                        console.error('Approval error:', err);
                                        alert('Error approving appointment');
                                    });
                                    });

                                    card.appendChild(info);
                                    card.appendChild(approveBtn);
                                    container.appendChild(card);
                                });
                                } else {
                                container.innerHTML = `
                                    <div style="padding: 20px; text-align: center; color: #6b7280;">
                                    <div style="font-size: 16px; margin-bottom: 8px;">📅</div>
                                    <p style="margin: 0; font-size: 14px;">No pending appointments found.</p>
                                    </div>
                                `;
                                }
                            })
                            .catch(error => {
                                console.error('Fetch error:', error);
                                container.innerHTML = `
                                <div style="padding: 20px; text-align: center; color: #6b7280;">
                                    <p style="margin: 0; font-size: 14px;">Error loading appointments.</p>
                                </div>
                                `;
                            });
                        }

                        loadPendingAppointments();
                        });
                        </script>
                 </div>
            </div>
        </div>

        <!-- Edit Appointment Modal -->
        <div id="editAppointmentModal" class="modal-overlay" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Appointment</h3>
                    <button id="closeEditModal" class="modal-close">×</button>
                </div>
                
                <form id="editAppointmentForm" class="modal-form">
                    <div class="form-group">
                        <label for="editPatientName">Patient Name</label>
                        <input type="text" id="editPatientName" name="patientName" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editPatientName">Appointment Time</label>
                        <input type="time" id="editAppointmentTime" name="appointmentTime" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editServiceType">Service Type</label>
                        <select id="editServiceType" name="serviceType" required>
                            <option value="">Select Service</option>
                            <option value="Pre-Natal Check Up">Pre-Natal Check Up</option>
                            <option value="Normal Delivery">Normal Delivery</option>
                            <option value="Pedia Check Up">Pedia Check Up</option>
                            <option value="Papsmear">Papsmear</option>
                            <option value="Family Planning">Family Planning</option>
                            <option value="BCG/HEPA-B Vaccine">BCG/HEPA-B Vaccine</option>
                            <option value="Newborn Screening">Newborn Screening</option>
                            <option value="Hearing Test">Hearing Test</option>
                            <option value="Post Partum Check Up">Post Partum Check Up</option>
                            <option value="Immunization">Immunization</option>
                            <option value="Prenatal Check-up">Prenatal Check-up</option>
                            <option value="Ultrasound">Ultrasound</option>
                            <option value="Blood Test">Blood Test</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editSelectedDoctor">Selected Doctor</label>
                        <select id="editSelectedDoctor" name="selectedDoctor" required>
                            <option value="">Select Doctor</option>
                            <option value="Dr. Maria Santos">Dr. Maria Santos</option>
                            <option value="Dr. Juan Rodriguez">Dr. Juan Rodriguez</option>
                            <option value="Dr. Ana Garcia">Dr. Ana Garcia</option>
                            <option value="Dr. Carlos Lopez">Dr. Carlos Lopez</option>
                            <option value="Dr. Sofia Martinez">Dr. Sofia Martinez</option>
                            <option value="Dr. Miguel Torres">Dr. Miguel Torres</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editNotes">Additional Notes</label>
                        <textarea id="editNotes" name="notes" rows="3" placeholder="Enter any additional notes..."></textarea>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" id="cancelEdit" class="btn-cancel-modal">Cancel</button>
                        <button type="submit" class="btn-save-modal">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        <!--NEW SERVICE TRY
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
                    <tbody id="patient-table-body">
                        <!-- Patient data will be populated from backend -->
                        <tr style="background: #fff; border-bottom: 1px solid #f3e6f7;">
                            <td colspan="6" style="padding: 40px 18px; text-align: center; color: #6b7280;">
                                <div style="font-size: 16px; margin-bottom: 8px;">📋</div>
                                <p style="margin: 0; font-size: 14px;">No patients found. Data will be loaded from backend.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <script>
        const getClerkPatientsURL = 'http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_get_patients.php';
        const getStatusListURL = 'http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_get_status_list.php';
        const setPatientStatusURL = 'http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_set_patient_status.php';

        document.addEventListener('DOMContentLoaded', () => {
        const tableBody = document.getElementById('patient-table-body');
        const searchInput = document.getElementById('patient-search');
        let statusOptions = [];

        // Fetch status options first
        function loadStatusOptions() {
            return fetch(getStatusListURL)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && Array.isArray(data.data)) {
                statusOptions = data.data;
                }
            })
            .catch(error => console.error('Status list fetch error:', error));
        }

        // Submit status update
        function updatePatientStatus(patientId, labelText) {
            fetch(setPatientStatusURL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                patient_id: patientId,
                status_label: labelText,
                remarks: '' // Optional: add remarks field
            })
            })
            .then(response => response.json())
            .then(result => {
            if (result.status === 'success') {
                alert('Status updated successfully');
                loadPatientList(); // Refresh table
            } else {
                alert('Failed to update status: ' + result.message);
            }
            })
            .catch(error => {
            console.error('Status update error:', error);
            alert('Error updating status');
            });
        }


        // Load and display patient list
        function loadPatientList() {
            fetch(getClerkPatientsURL)
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = '';

                if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
                data.data.forEach(patient => {
                    const currentStatus = patient.status_label || 'No Status';
                    const row = document.createElement('tr');

                    // Build status dropdown
                    const statusDropdown = document.createElement('select');
                    statusDropdown.style.padding = '6px 10px';
                    statusDropdown.style.borderRadius = '6px';
                    statusDropdown.style.border = '1px solid #ccc';
                    statusDropdown.style.fontSize = '0.95rem';

                    statusOptions.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.status_label;
                    opt.textContent = option.status_label;
                    if (option.status_label === currentStatus) opt.selected = true;
                    statusDropdown.appendChild(opt);
                    });

                    statusDropdown.addEventListener('change', () => {
                    updatePatientStatus(patient.patient_id, statusDropdown.value);
                    });

                row.innerHTML = `
                <td style="padding: 14px 18px;">${patient.patient_id}</td>
                <td style="padding: 14px 18px;">${patient.first_name} ${patient.last_name}</td>
                <td style="padding: 14px 18px;">${patient.age || '-'}</td>
                <td style="padding: 14px 18px;">${patient.gender || '-'}</td>
                <td style="padding: 14px 18px;"></td>
                <td style="padding: 14px 18px;">
                    <!-- View Billing Link -->
                    <a href="#"
                    style="color: #2d0b3a; text-decoration: underline; font-weight: 500;"
                    onclick="
                        event.preventDefault();
                        document.getElementById('patient_id_hidden').value = '${patient.patient_id}';
                        document.getElementById('patient-list-section').style.display = 'none';
                        document.getElementById('services-section').style.display = 'none';
                        document.getElementById('billingDashboard').style.display = 'block';
                        loadBillingTransactions(${patient.patient_id});
                        populateBillingPage('${patient.first_name} ${patient.last_name}', '${patient.patient_id}');
                        document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                        Array.from(document.querySelectorAll('.nav-item')).find(nav => nav.textContent.trim().includes('Billing'))?.classList.add('active');
                    "
                    >
                    View Billing
                    </a>
                    <br>
                    <!-- View Profile Button -->
                    <button onclick="viewPatientProfile(${patient.patient_id})"
                    style="margin-top: 8px; background-color: #3498db; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">
                    View Profile
                    </button>
                </td>
                `;
                    row.cells[4].appendChild(statusDropdown);
                    tableBody.appendChild(row);
                });
                } else {
                tableBody.innerHTML = `
                    <tr>
                    <td colspan="6" style="padding: 40px 18px; text-align: center; color: #6b7280;">
                        <div style="font-size: 16px; margin-bottom: 8px;">📋</div>
                        <p style="margin: 0; font-size: 14px;">No patients found.</p>
                    </td>
                    </tr>
                `;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                tableBody.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 40px 18px; text-align: center; color: #6b7280;">
                    <p style="margin: 0; font-size: 14px;">Error loading patient data.</p>
                    </td>
                </tr>
                `;
            });
        }

        // Initialize section and load everything
        document.getElementById('patient-list-section').style.display = 'block';
        loadStatusOptions().then(loadPatientList);
        });
        </script>


    <!-- Update Patient Modal (copied from pdash.php) -->
    <div id="updateModal" class="modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(44,62,80,0.18); z-index: 9999; align-items: center; justify-content: center;">
      <div class="modal-content" style="max-width: 650px; background: linear-gradient(135deg, #f8f9fe 60%, #e9d8fd 100%); box-shadow: 0 8px 32px rgba(108, 51, 110, 0.18); border-radius: 24px; padding: 0; overflow-y: auto; max-height: 90vh;">
        <div style="background: linear-gradient(90deg, #7c3aed 0%, #ff6ba2 100%); padding: 28px 36px 18px 36px; border-radius: 24px 24px 0 0;">
          <h2 style="color: #fff; font-size: 28px; font-weight: 700; margin: 0; letter-spacing: 1px;">Update Patient Information</h2>
        </div>
        <form id="updateForm" style="padding: 32px 36px 24px 36px;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px 32px; margin-bottom: 28px;">
            <div style="grid-column: 1 / -1; font-size: 18px; color: #7c3aed; font-weight: 600; margin-bottom: 8px;">Patient</div>
            <div style="display: flex; justify-content: center; align-items: center; margin-top: -40px; margin-bottom: 24px;">
              <div style="position: relative; width: 90px; height: 90px;">
                <img id="updatePatientPhoto" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSI0MCIgY3k9IjQwIiByPSI0MCIgZmlsbD0iI0Y3RkFGQyIvPjxjaXJjbGUgY3g9IjQwIiBjeT0iMzAiIHI9IjEyIiBmaWxsPSIjNEE1NTY4Ii8+PHBhdGggZD0iTTIwIDYwYzAtMTEgOS0yMCAyMC0yMHMyMCA5IDIwIDIwdjEwSDIwVjYweiIgZmlsbD0iIzRBNTU2OCIvPjwvc3ZnPg==" alt="Patient Photo" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #e2e8f0; background: #f7fafc;">
                <label for="updatePhotoInput" style="position: absolute; bottom: 0; right: 0; background: linear-gradient(90deg, #ff6ba2 0%, #667eea 100%); color: #fff; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(108, 51, 110, 0.12); font-size: 18px; border: 2px solid #fff;">
                  <span style="pointer-events: none;">&#128247;</span>
                  <input id="updatePhotoInput" type="file" accept="image/*" style="display: none;">
                </label>
              </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">First Name</label>
              <input type="text" name="firstName" required style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff; transition: border 0.2s;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Middle Name</label>
              <input type="text" name="middleName" style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff; transition: border 0.2s;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Last Name</label>
              <input type="text" name="lastName" required style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff; transition: border 0.2s;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Date of Birth</label>
              <input type="date" name="dob" required style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff; transition: border 0.2s;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Age</label>
              <input type="number" name="age" min="0" required style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff; transition: border 0.2s;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Gender</label>
              <select name="gender" required style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff;">
                <option value="">Select Gender</option>
                <option value="Female">Female</option>
                <option value="Male">Male</option>
              </select>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Status</label>
              <select name="status" required style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff;">
                <option value="">Select Status</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
                <option value="Separated">Separated</option>
              </select>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Contact Number</label>
              <input type="text" name="contact" required style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff; transition: border 0.2s;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Occupation</label>
              <input type="text" name="occupation" style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff; transition: border 0.2s;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px; grid-column: 1 / -1;">
              <label style="color: #7c3aed; font-weight: 500;">Address</label>
              <input type="text" name="address" style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff; transition: border 0.2s;">
            </div>
            <div style="grid-column: 1 / -1; font-size: 18px; color: #7c3aed; font-weight: 600; margin: 18px 0 8px 0;">Emergency Contact</div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Name</label>
              <input type="text" name="emergencyName" required style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff; transition: border 0.2s;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Contact Number</label>
              <input type="text" name="emergencyContact" required style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff; transition: border 0.2s;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Address</label>
              <input type="text" name="emergencyAddress" style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff; transition: border 0.2s;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="color: #7c3aed; font-weight: 500;">Relationship</label>
              <select name="relationship" required style="padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0; font-size: 16px; background: #fff;">
                <option value="">Select Relationship</option>
                <option value="Parent">Parent</option>
                <option value="Sibling">Sibling</option>
                <option value="Spouse">Spouse</option>
                <option value="Friend">Friend</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>
          <div style="display:flex; justify-content: flex-end; gap: 10px; margin-top: 18px;">
            <button type="button" class="btn-secondary" id="closeUpdateModal" style="padding: 10px 22px; border-radius: 8px; font-size: 15px;">Cancel</button>
            <button type="submit" class="btn-primary" style="padding: 10px 28px; border-radius: 8px; font-size: 16px; font-weight: 600; background: linear-gradient(90deg, #ff6ba2 0%, #667eea 100%);">Save</button>
          </div>
                    </form>
                </div>
            </div>

    <!-- Visit Analytics Modal -->
    <div id="visitAnalyticsModal" class="modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(44,62,80,0.18); z-index: 9999; align-items: center; justify-content: center;">
      <div class="modal-content" style="max-width: 700px; width: 100%; background: #fff; box-shadow: 0 8px 32px rgba(108, 51, 110, 0.18); border-radius: 20px; padding: 0; overflow-y: auto; max-height: 80vh; min-height: 420px;">
        <div style="background: linear-gradient(90deg, #7c3aed 0%, #ff6ba2 100%); padding: 22px 32px 12px 32px; border-radius: 20px 20px 0 0;">
          <h2 style="color: #fff; font-size: 22px; font-weight: 700; margin: 0; letter-spacing: 1px;">Add Visit Analytics</h2>
        </div>
        <form id="visitAnalyticsForm" style="padding: 28px 32px 18px 32px; display: grid; grid-template-columns: 1fr 1fr; gap: 18px 32px;">
          <div style="display: flex; flex-direction: column; gap: 6px; grid-column: 1 / 2;">
            <label style="color: #7c3aed; font-weight: 500;">Visit Date</label>
            <input type="date" name="visit_date" required style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff;">
          </div>
          <div style="display: flex; flex-direction: column; gap: 6px; grid-column: 2 / 3;">
            <label style="color: #7c3aed; font-weight: 500;">BP</label>
            <input type="text" name="bp" required style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff;">
          </div>
          <div style="display: flex; flex-direction: column; gap: 6px; grid-column: 1 / 2;">
            <label style="color: #7c3aed; font-weight: 500;">Temp</label>
            <input type="text" name="temp" required style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff;">
          </div>
          <div style="display: flex; flex-direction: column; gap: 6px; grid-column: 2 / 3;">
            <label style="color: #7c3aed; font-weight: 500;">Weight</label>
            <input type="text" name="weight" required style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff;">
          </div>
          <div style="display: flex; flex-direction: column; gap: 6px; grid-column: 1 / 2;">
            <label style="color: #7c3aed; font-weight: 500;">Fundal Height</label>
            <input type="text" name="fundal_height" required style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff;">
          </div>
          <div style="display: flex; flex-direction: column; gap: 6px; grid-column: 2 / 3;">
            <label style="color: #7c3aed; font-weight: 500;">Fetal Heart Tone</label>
            <input type="text" name="fetal_heart_tone" required style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff;">
          </div>
          <div style="display: flex; flex-direction: column; gap: 6px; grid-column: 1 / 2;">
            <label style="color: #7c3aed; font-weight: 500;">Fetal Position</label>
            <input type="text" name="fetal_position" required style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff;">
          </div>
          <div style="display: flex; flex-direction: column; gap: 6px; grid-column: 2 / 3;">
            <label style="color: #7c3aed; font-weight: 500;">Chief Complaint</label>
            <input type="text" name="chief_complaint" required style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff;">
          </div>
          <div style="display:flex; justify-content: flex-end; gap: 10px; margin-top: 10px; grid-column: 1 / -1;">
            <button type="button" class="btn-secondary" id="closeVisitAnalyticsModal" style="padding: 8px 18px; border-radius: 8px; font-size: 14px;">Cancel</button>
            <button type="submit" class="btn-primary" style="padding: 8px 22px; border-radius: 8px; font-size: 15px; font-weight: 600; background: linear-gradient(90deg, #ff6ba2 0%, #667eea 100%);">Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Physical Examination Add New Modal -->
    <div id="physicalExamModal" class="modal-overlay" style="display:none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(44,62,80,0.18); z-index: 9999; align-items: center; justify-content: center;">
      <div class="modal-content" style="max-width: 800px; width: 100%; background: #fff; box-shadow: 0 8px 32px rgba(108, 51, 110, 0.18); border-radius: 8px; padding: 0; overflow-y: auto; max-height: 80vh; min-height: 220px;">
        <form id="physicalExamForm" style="padding: 0 32px 18px 32px; display: flex; flex-direction: column; gap: 18px;">
          <div id="physicalExamPage1">
            <div style="background: linear-gradient(90deg, #7c3aed 0%, #ff6ba2 100%); padding: 22px 32px 12px 32px; border-radius: 8px 8px 0 0; margin: 0 -32px 18px -32px;">
              <h2 style="color: #fff; font-size: 20px; font-weight: 700; margin: 0; letter-spacing: 1px;">Add Physical Examination</h2>
            </div>
            <div>
              <label style="color: #7c3aed; font-weight: 600; font-size: 16px; margin-bottom: 8px; display: block;">Visit Date</label>
              <input type="date" name="visit_date" required style="padding: 8px 12px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff; margin-bottom: 16px;">
            </div>
            <div>
              <label style="color: #7c3aed; font-weight: 600; font-size: 16px; margin-bottom: 8px; display: block;">Physical Examination</label>
              <div style="margin-bottom: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px 32px;">
                <div style="min-width: 180px;">
                  <span style="color: #7c3aed; font-weight: 500; display: block; margin-bottom: 6px;">Conjunctiva:</span>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="conjunctiva[]" value="Pale" style="accent-color: #7c3aed;"> Pale</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="conjunctiva[]" value="Yellowish" style="accent-color: #7c3aed;"> Yellowish</label>
                </div>
                <div style="min-width: 180px;">
                  <span style="color: #7c3aed; font-weight: 500; display: block; margin-bottom: 6px;">Neck:</span>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="neck[]" value="Thyroid" style="accent-color: #7c3aed;"> Enlarged Thyroid</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="neck[]" value="Nodes" style="accent-color: #7c3aed;"> Enlarged Lymph Nodes</label>
                </div>
                <div style="min-width: 220px;">
                  <span style="color: #7c3aed; font-weight: 500; display: block; margin-bottom: 6px;">Breast (Left):</span>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="breast_left[]" value="mass" style="accent-color: #7c3aed;"> Mass</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="breast_left[]" value="nipple" style="accent-color: #7c3aed;"> Nipple Discharge</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="breast_left[]" value="skin" style="accent-color: #7c3aed;"> Skin: orange peel or dimpling</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="breast_left[]" value="axillary" style="accent-color: #7c3aed;"> Enlarged axillary lymph nodes</label>
                </div>
                <div style="min-width: 220px;">
                  <span style="color: #7c3aed; font-weight: 500; display: block; margin-bottom: 6px;">Breast (Right):</span>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="breast_right[]" value="mass" style="accent-color: #7c3aed;"> Mass</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="breast_right[]" value="nipple" style="accent-color: #7c3aed;"> Nipple Discharge</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="breast_right[]" value="skin" style="accent-color: #7c3aed;"> Skin: orange peel or dimpling</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="breast_right[]" value="axillary" style="accent-color: #7c3aed;"> Enlarged axillary lymph nodes</label>
                </div>
                <div style="min-width: 220px;">
                  <span style="color: #7c3aed; font-weight: 500; display: block; margin-bottom: 6px;">Thorax:</span>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="thorax[]" value="heart" style="accent-color: #7c3aed;"> Abnormal heart sounds/cardiac rate</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="thorax[]" value="breath" style="accent-color: #7c3aed;"> Abnormal breath sounds/respiratory rate</label>
                </div>
                <div style="min-width: 220px;">
                  <span style="color: #7c3aed; font-weight: 500; display: block; margin-bottom: 6px;">Abdomen:</span>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="abdomen[]" value="liver" style="accent-color: #7c3aed;"> Enlarged Liver</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="abdomen[]" value="mass" style="accent-color: #7c3aed;"> Mass</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="abdomen[]" value="tenderness" style="accent-color: #7c3aed;"> Tenderness</label>
                </div>
                <div style="min-width: 220px;">
                  <span style="color: #7c3aed; font-weight: 500; display: block; margin-bottom: 6px;">Extremities:</span>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="extremities[]" value="edema" style="accent-color: #7c3aed;"> Edema</label>
                  <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="extremities[]" value="varicosities" style="accent-color: #7c3aed;"> Varicosities</label>
                </div>
              </div>
            </div>
            <div style="display:flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
              <button type="button" class="btn-secondary" id="closePhysicalExamModal" style="padding: 8px 18px; border-radius: 8px; font-size: 14px;">Cancel</button>
              <button type="button" class="btn-primary" id="nextPhysicalExamModal" style="padding: 8px 22px; border-radius: 8px; font-size: 15px; font-weight: 600; background: linear-gradient(90deg, #ff6ba2 0%, #667eea 100%);">Next</button>
            </div>
          </div>
          <div id="physicalExamPage2" style="display:none;">
            <div style="background: linear-gradient(90deg, #7c3aed 0%, #ff6ba2 100%); padding: 22px 32px 12px 32px; border-radius: 8px 8px 0 0; margin: 0 -32px 18px -32px;">
              <h2 style="color: #fff; font-size: 20px; font-weight: 700; margin: 0; letter-spacing: 1px;">Add Pelvic Examination</h2>
            </div>
            <div style="margin-bottom: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px 32px;">
              <div style="min-width: 220px;">
                <span style="color: #7c3aed; font-weight: 500; display: block; margin-bottom: 6px;">Perinium:</span>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="perinium[]" value="scars" style="accent-color: #7c3aed;"> Scars</label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="perinium[]" value="warts" style="accent-color: #7c3aed;"> Warts</label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="perinium[]" value="reddish" style="accent-color: #7c3aed;"> Reddish</label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="perinium[]" value="lacerations" style="accent-color: #7c3aed;"> Lacerations</label>
              </div>
              <div style="min-width: 220px;">
                <span style="color: #7c3aed; font-weight: 500; display: block; margin-bottom: 6px;">Vagina:</span>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="vagina[]" value="congested" style="accent-color: #7c3aed;"> Congested</label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="vagina[]" value="cyst" style="accent-color: #7c3aed;"> Bartholin's Cyst</label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="vagina[]" value="warts" style="accent-color: #7c3aed;"> Warts</label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="vagina[]" value="gland" style="accent-color: #7c3aed;"> Skene's Gland Discharge</label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="vagina[]" value="recto" style="accent-color: #7c3aed;"> Rectocoele</label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="vagina[]" value="cysto" style="accent-color: #7c3aed;"> Cystocoele</label>
              </div>
              <!-- Add more pelvic exam fields here as needed, in the second column -->
               <div style="min-width: 220px;">
               <span style="color: #7c3aed; font-weight: 500; display: block; margin-bottom: 6px;">Cervix:</span>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="cervix[]" value="congested" style="accent-color: #7c3aed;">Congested</label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="cervix[]" value="erosion" style="accent-color: #7c3aed;">Erosion</label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="cervix[]" value="congested" style="accent-color: #7c3aed;">Congested</label>
                </div>
              <div style="min-width: 220px;">
                <span style="color: #7c3aed; font-weight: 500; display: block; margin-bottom: 6px;">ADNEXA:</span>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="adnexa[]" value="mass" style="accent-color: #7c3aed;"> Mass</label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 15px;"><input type="checkbox" name="adnexa[]" value="tenderness" style="accent-color: #7c3aed;"> Tenderness</label>
              </div>
            </div>
            <div style="display:flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
              <button type="button" class="btn-secondary" id="backPhysicalExamModal" style="padding: 8px 18px; border-radius: 8px; font-size: 14px;">Back</button>
              <button type="submit" class="btn-primary" style="padding: 8px 22px; border-radius: 8px; font-size: 15px; font-weight: 600; background: linear-gradient(90deg, #ff6ba2 0%, #667eea 100%);">Save</button>
            </div>
          </div>
        </form>
        </div>
    </div>
<!-- Billing Dashboard Section (hidden by default) -->
<div id="billingDashboard" class="content-section" style="display:none; flex:1; background:#f9fafc; overflow-y:auto; max-height:calc(100vh - 40px); padding:0 24px;">
  <input type="hidden" id="patient_id_hidden">
  <div style="padding:32px 0; margin:0 auto;">

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
      <h1 style="font-size: 36px; color: #232b3b; font-weight: 700; margin: 0;">Billing Dashboard</h1>
    </div>

    <!-- Summary Boxes -->
    <div style="display: flex; gap: 32px; margin-bottom: 32px;">
      <div class="balance-box" style="background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(44,62,80,0.08); padding:28px 36px; flex:1; display:flex; flex-direction:column; align-items:flex-start;">
        <div style="font-size:15px; color:#7c8ba1; font-weight:500;">Outstanding Balance</div>
        <div id="outstandingBalance" style="font-size:28px; font-weight:700; color:#e53e3e; margin-top:6px;">₱0.00</div>
      </div>
      <div style="background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(44,62,80,0.08); padding:28px 36px; flex:1; display:flex; flex-direction:column; align-items:flex-start;">
        <div style="font-size:15px; color:#7c8ba1; font-weight:500;">Last Payment</div>
        <div id="lastPaymentAmount" style="font-size:22px; font-weight:700; color:#232b3b; margin-top:6px;">₱0.00</div>
        <div id="lastPaymentDate" style="font-size:13px; color:#a0aec0; margin-top:2px;">--</div>
      </div>
   
    </div>

    <!-- Add Bill Modal -->
    <div id="addBillModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:10000; display:flex; align-items:center; justify-content:center;">
      <div style="background:#fff; padding:24px 32px; border-radius:12px; width:400px; position:relative;">
        <button onclick="document.getElementById('addBillModal').style.display='none'" style="position:absolute; top:12px; right:12px; background:none; border:none; font-size:20px; color:#4a5568;">×</button>
        <h2 style="margin-bottom:16px; font-size:20px;">Add Billing Transaction</h2>
        <input type="text" id="billDescription" placeholder="Description" style="width:100%; margin-bottom:10px; padding:8px;">
        <input type="number" id="billAmount" placeholder="Amount" style="width:100%; margin-bottom:10px; padding:8px;">
          <select id="billServiceId" style="width:100%; margin-bottom:16px; padding:8px;">
        <option value="">Select Service</option>
        <!-- Options will be injected dynamically -->
        </select>
        <select id="billType" style="width:100%; margin-bottom:16px; padding:8px;">
          <option value="charge">Charge</option>
          <option value="discount">Discount</option>
          <option value="payment">Payment</option>
        </select>
        <button onclick="submitBill()" class="btn-primary" style="width:100%; padding:10px;">Submit</button>
      </div>
    </div>

    <!-- Transactions Table -->
    <div style="background:#fff; border-radius:18px; box-shadow:0 2px 12px rgba(44,62,80,0.06); padding:28px 32px; margin-bottom:32px;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <div style="font-size:20px; color:#232b3b; font-weight:700;">Recent Transactions</div>
        <button id="addBillBtn" class="btn-primary" style="padding:10px 28px; font-size:15px; border-radius:8px;">Add Bill</button>
      </div>
      <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:15px;">
          <thead>
            <tr style="background:#f8f9fe; color:#7c3aed;">
              <th style="padding:8px 10px; text-align:left;">Date</th>
              <th style="padding:8px 10px; text-align:left;">Description</th>
              <th style="padding:8px 10px; text-align:left;">Amount</th>
              <th style="padding:8px 10px; text-align:left;">Status</th>
            </tr>
          </thead>
          <tbody id="billingTransactionTable">
            <!-- Awaiting backend data -->
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
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

                        fetch('http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_change_password.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            credentials: 'same-origin',
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
<script>
// Trigger modal and load services
document.getElementById('addBillBtn')?.addEventListener('click', () => {
  const patientId = document.getElementById('patient_id_hidden')?.value;
  if (patientId) {
    loadServiceOptions(patientId);
    document.getElementById('addBillModal').style.display = 'flex';
  } else {
    alert('Missing patient context. Please select a patient first.');
  }
});

// Submit billing transaction
function submitBill() {
  const description = document.getElementById('billDescription').value.trim();
  const amount = parseFloat(document.getElementById('billAmount').value);
  const type = document.getElementById('billType').value;
  const patientId = document.getElementById('patient_id_hidden')?.value;
  const serviceId = document.getElementById('billServiceId')?.value;

  if (!description || isNaN(amount) || !patientId || !serviceId) {
    alert('Please enter valid description, amount, patient, and service.');
    return;
  }

  const payload = {
    service_id: serviceId,
    patient_id: patientId,
    transaction_type: type,
    description: description,
    amount: amount
  };

  fetch('http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_set_bill_transact.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      document.getElementById('addBillModal').style.display = 'none';
      loadBillingTransactions(patientId);
      refreshBalance(patientId);
    } else {
      alert(data.message);
    }
  })
  .catch(err => {
    console.error('❌ Bill submission error:', err);
    alert('Failed to submit bill.');
  });
}

// Initialize billing page
function populateBillingPage(patientName, patientId) {
  const header = document.querySelector('#billingDashboard h1');
  if (header) {
    header.textContent = `Billing Dashboard for ${escapeHtml(patientName)}`;
  }

  const hiddenInput = document.getElementById('patient_id_hidden');
  if (hiddenInput) {
    hiddenInput.value = patientId;
  }

  // Load all billing data
  loadBillingTransactions(patientId);
  refreshBalance(patientId);
  loadServiceOptions(patientId);
}
function viewPatientProfile(patientId) {
  fetch('auth/action/clerk/clerk_set_selected_patient.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    credentials: 'same-origin',
    body: JSON.stringify({ selectedPatientId: patientId })
  })
  .then(res => res.json())
  .then(response => {
    if (response.success) {
      window.location.href = 'auth/action/clerk/clerk_view_patient.php';
    } else {
      alert('Failed to set patient session. Please try again.');
    }
  })
  .catch(err => {
    console.error('Error setting patient session:', err);
    alert('Server error. Please try again later.');
  });
}
// Load billing transactions
function loadBillingTransactions(patientId) {
  const tbody = document.getElementById('billingTransactionTable');
  tbody.innerHTML = `<tr><td colspan="4" style="padding:10px; color:#6b7280;">Loading...</td></tr>`;

  fetch(`http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_get_bill_transact.php?patient_id=${patientId}`)
    .then(res => res.json())
    .then(data => {
      tbody.innerHTML = '';
      if (data.status === 'success') {
        if (data.data.length === 0) {
          tbody.innerHTML = `<tr><td colspan="4" style="padding:10px; color:#9ca3af;">No transactions found.</td></tr>`;
          return;
        }

        data.data.forEach(tx => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td style="padding:8px 10px;">${tx.date}</td>
            <td style="padding:8px 10px;">${escapeHtml(tx.description)}</td>
            <td style="padding:8px 10px;">₱${parseFloat(tx.amount).toFixed(2)}</td>
            <td style="padding:8px 10px;">${tx.transaction_type}</td>
          `;
          tbody.appendChild(row);
        });
      } else {
        tbody.innerHTML = `<tr><td colspan="4" style="padding:10px; color:#ef4444;">${escapeHtml(data.message)}</td></tr>`;
      }
    })
    .catch(err => {
      console.error('❌ Billing fetch error:', err);
      tbody.innerHTML = `<tr><td colspan="4" style="padding:10px; color:#ef4444;">Failed to load transactions.</td></tr>`;
    });
}

// Refresh balance box
function refreshBalance(patientId) {
  fetch(`http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_get_balance.php?patient_id=${patientId}`)
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        document.getElementById('outstandingBalance').textContent = `₱${parseFloat(data.total_balance).toFixed(2)}`;
      }
    })
    .catch(err => {
      console.error('❌ Balance fetch error:', err);
    });
}

// Load service options for billing
function loadServiceOptions(patientId) {
  fetch(`http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_get_services_by_patient.php?patient_id=${patientId}`)
    .then(res => res.json())
    .then(data => {
      const select = document.getElementById('billServiceId');
      select.innerHTML = '<option value="">Select Service</option>';
      if (data.status === 'success') {
        data.data.forEach(service => {
          const option = document.createElement('option');
          option.value = service.service_id;
          option.textContent = `${service.service_type} (${service.service_date})`;
          select.appendChild(option);
        });
      }
    })
    .catch(err => {
      console.error('❌ Service fetch error:', err);
    });
}

// Escape HTML for safe rendering
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
</script>

          </div>
        </div>   
    <!-- Services Dashboard Section (hidden by default) -->
	<div id="services-section" class="content-section" style="margin-top: 30px; display: none;">
		<h2 style="color: #6c336e; font-size: 2rem; font-weight: bold; margin-bottom: 24px; letter-spacing: 1px;" id="patientServicesHeader">PATIENT SERVICES</h2>
		
		<!-- Blank State - Default Display -->
		<div id="services-blank-state" style="text-align: center; padding: 80px 20px; color: #6b7280;">
			<div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto 32px auto; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);">
				<span style="font-size: 48px;">🏥</span>
			</div>
			<h3 style="color: #374151; font-size: 1.5rem; font-weight: 600; margin: 0 0 16px 0;">No Services Added Yet</h3>
			<p style="color: #6b7280; font-size: 16px; margin: 0 0 32px 0; line-height: 1.6;">Services will appear here once you add them from the Patient List page.<br>Click "Add Service" on any patient to get started.</p>
			
			<!-- Search Bar for Blank State -->
			<div style="margin: 32px auto; max-width: 500px; text-align: left;">
				<div style="position: relative; margin-bottom: 16px;">
					<input 
						type="text" 
						id="services-search-blank" 
						placeholder="Search by Patient Name or ID..." 
						style="width: 100%; padding: 16px 20px; padding-left: 50px; border-radius: 16px; border: 2px solid #e5e7eb; font-size: 16px; outline: none; transition: all 0.3s ease; background: white; box-shadow: 0 4px 16px rgba(108,51,110,0.08);"
						oninput="searchPatientsFromBlank()"
					>
					<div style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px;">
						🔍
					</div>
				</div>
				
				<!-- Search Results for Blank State -->
				<div id="search-results-blank" style="display: none; background: white; border-radius: 12px; box-shadow: 0 4px 16px rgba(108,51,110,0.07); overflow: hidden; margin-top: 16px;">
					<div style="background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 100%); padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
						<h4 style="margin: 0; color: #374151; font-size: 16px; font-weight: 600;">Search Results</h4>
					</div>
					<div id="search-results-content-blank" style="max-height: 300px; overflow-y: auto;">
						<!-- Search results will be populated here -->
					</div>
				</div>
			</div>
			
		
		</div>
        	<button class="btn-primary" id="openAddServiceModal" style="padding: 16px 32px; font-size: 16px; font-weight: 600; border-radius: 12px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white; border: none; cursor: pointer; box-shadow: 0 4px 16px rgba(102, 126, 234, 0.2); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(102, 126, 234, 0.2)';">
				+ Add New Service
			</button>

<script>
function loadPatientServices(patientId, patientName) {
  fetch(`http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_get_services_by_patient.php?patient_id=${patientId}`)
    .then(res => res.json())
    .then(data => {
      const section = document.getElementById('services-section');
      const header = document.getElementById('patientServicesHeader');
      const blankState = document.getElementById('services-blank-state');

      header.textContent = `Patient Services for ${patientName}`;
      section.style.display = 'block';

      // Remove any previous service table or error block
      const oldTable = document.getElementById('patient-services-table');
      if (oldTable) oldTable.remove();
      const oldError = document.getElementById('patient-services-error');
      if (oldError) oldError.remove();

      if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
        blankState.style.display = 'none';

        const tableWrapper = document.createElement('div');
        tableWrapper.id = 'patient-services-table';
        tableWrapper.style.marginTop = '40px';
        tableWrapper.style.background = 'rgba(255,255,255,0.95)';
        tableWrapper.style.borderRadius = '15px';
        tableWrapper.style.padding = '25px';
        tableWrapper.style.boxShadow = '0 4px 16px rgba(0,0,0,0.06)';

        const tableHTML = `
          <h3 style="color: #6c336e; margin-bottom: 18px;">Services for ${patientName}</h3>
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #f3e6f7; color: #6c336e;">
                <th style="padding: 10px 8px; text-align: left; border-radius: 8px 0 0 8px;">Service Date</th>
                <th style="padding: 10px 8px; text-align: left;">Service Type</th>
                <th style="padding: 10px 8px; text-align: left;">Amount</th>
                <th style="padding: 10px 8px; text-align: left;">Doctor</th>
                <th style="padding: 10px 8px; text-align: left; border-radius: 0 8px 8px 0;">Clinical Notes</th>
              </tr>
            </thead>
            <tbody>
              ${data.data.map(service => `
                <tr style="border-bottom: 1px solid #eee;">
                  <td style="padding: 10px 8px;">${service.service_date}</td>
                  <td style="padding: 10px 8px;">${service.service_type}</td>
                  <td style="padding: 10px 8px;">₱${parseFloat(service.service_amount || 0).toFixed(2)}</td>
                  <td style="padding: 10px 8px;">${service.doctor_name || '—'}</td>
                  <td style="padding: 10px 8px;">${(service.notes || '').replace(/\n/g, '<br>')}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        `;

        tableWrapper.innerHTML = tableHTML;
        blankState.insertAdjacentElement('afterend', tableWrapper);
        console.log(`✅ Services loaded for patient ${patientId}:`, data.data);
      } else {
        blankState.style.display = 'none';

        const errorBlock = document.createElement('div');
        errorBlock.id = 'patient-services-error';
        errorBlock.style.marginTop = '40px';
        errorBlock.style.background = '#fff0f0';
        errorBlock.style.borderRadius = '12px';
        errorBlock.style.padding = '24px';
        errorBlock.style.boxShadow = '0 4px 16px rgba(255,0,0,0.1)';
        errorBlock.style.color = '#b91c1c';
        errorBlock.style.fontSize = '16px';
        errorBlock.innerHTML = `
          <h3 style="margin-bottom: 12px;">⚠️ Unable to load services</h3>
          <p>${data.message || 'No services found or an error occurred while retrieving data.'}</p>
        `;

        blankState.insertAdjacentElement('afterend', errorBlock);
        console.warn(`⚠️ No services found or error for patient ${patientId}:`, data.message);
      }
    })
    .catch(err => {
      console.error('❌ Fetch error while loading services:', err);

      const section = document.getElementById('services-section');
      const blankState = document.getElementById('services-blank-state');
      blankState.style.display = 'none';

      const errorBlock = document.createElement('div');
      errorBlock.id = 'patient-services-error';
      errorBlock.style.marginTop = '40px';
      errorBlock.style.background = '#fff0f0';
      errorBlock.style.borderRadius = '12px';
      errorBlock.style.padding = '24px';
      errorBlock.style.boxShadow = '0 4px 16px rgba(255,0,0,0.1)';
      errorBlock.style.color = '#b91c1c';
      errorBlock.style.fontSize = '16px';
      errorBlock.innerHTML = `
        <h3 style="margin-bottom: 12px;">⚠️ Failed to load patient services</h3>
        <p>There was a problem connecting to the server. Please try again later.</p>
      `;

      section.appendChild(errorBlock);
    });
}
// Load service catalog and update dropdown
fetch('http://localhost/JAM_LYINGIN/auth/action/clerk/clerk_get_service_catalog.php')
  .then(res => res.json())
  .then(catalog => {
    const dropdown = document.getElementById('service_type_1');
    const priceInput = document.getElementById('service_amount_1');

    if (!dropdown || !priceInput) {
      console.warn('⚠️ Service dropdown or price input not found in DOM.');
      return;
    }

    dropdown.innerHTML = '<option value="">Select a service</option>';

    if (catalog.status === 'success' && Array.isArray(catalog.data)) {
      catalog.data.forEach(service => {
        const option = document.createElement('option');
        option.value = service.service_type_id;
        option.textContent = service.service_name;
        option.dataset.price = service.default_price;
        dropdown.appendChild(option);
      });

      // Auto-update price when service is selected
      dropdown.addEventListener('change', () => {
        const selected = dropdown.options[dropdown.selectedIndex];
        const price = selected.dataset.price || '0.00';
        priceInput.value = parseFloat(price).toFixed(2);
      });
    } else {
      console.warn('⚠️ Failed to load service catalog:', catalog.message);
    }
  })
  .catch(err => {
    console.error('❌ Error loading service catalog:', err);
  });

</script>

		<!-- Patient Info and Services Table - Hidden by Default -->
		<div id="services-content" style="display: none;">
						<!-- Back to Patient List Button and Search Bar -->
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
				<button onclick="goBackToPatientList()" style="background: linear-gradient(90deg, #6b7280 0%, #4b5563 100%); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
					← Back to Patient List
				</button>
				
				<!-- Search Bar -->
				<div style="position: relative; min-width: 300px;">
					<input 
						type="text" 
						id="services-search" 
						placeholder="Search by Patient Name or ID..." 
						style="width: 100%; padding: 12px 16px; padding-left: 44px; border-radius: 12px; border: 2px solid #e5e7eb; font-size: 14px; outline: none; transition: all 0.3s ease; background: white; box-shadow: 0 2px 8px rgba(108,51,110,0.04);"
						oninput="searchPatients()"
					>
					<div style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af;">
						🔍
					</div>
				</div>
			</div>
			
			<!-- Search Results Section -->
			<div id="search-results" style="display: none; margin-bottom: 24px; background: white; border-radius: 12px; box-shadow: 0 4px 16px rgba(108,51,110,0.07); overflow: hidden;">
				<div style="background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 100%); padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
					<h4 style="margin: 0; color: #374151; font-size: 16px; font-weight: 600;">Search Results</h4>
				</div>
				<div id="search-results-content" style="max-height: 300px; overflow-y: auto;">
					<!-- Search results will be populated here -->
				</div>
			</div>
			
			<!-- Patient Info and Add Service Button -->
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #f3e6f7;">
			<div style="display: flex; align-items: center; gap: 16px;">
				<div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold; box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);">
                    👩‍💼
				</div>
				<div>
					<h3 id="services-patient-name" style="color: #6c336e; font-size: 1.5rem; font-weight: 600; margin: 0 0 4px 0;">Patient Name</h3>
					<p id="services-patient-id" style="color: #666; font-size: 14px; margin: 0;">Patient ID: PAT-0000-000</p>
				</div>
			</div>
			<button class="btn-primary" id="openAddServiceModalFromServices" style="padding: 12px 24px; font-size: 16px; font-weight: 600; border-radius: 10px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white; border: none; cursor: pointer; box-shadow: 0 4px 16px rgba(102, 126, 234, 0.2); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 16px rgba(102, 126, 234, 0.2)';">
				+ Add Service
			</button>
		</div>
		
		<!-- Services Table Container -->
		<div style="overflow-x: auto;">
			<table id="services-table" style="width: 100%; border-collapse: separate; border-spacing: 0; min-width: 700px; border-radius: 18px; overflow: hidden; box-shadow: 0 4px 16px rgba(108,51,110,0.07);">
                <thead>
                <tr style="background: linear-gradient(90deg, rgb(251, 137, 184) 0%, #764ba2 100%); color: #fff;">
                    <th style="padding: 16px 18px; text-align: left; font-size: 1.08rem; font-weight: bold; border-radius: 18px 0 0 0;">
                    Date
                    </th>
                    <th style="padding: 16px 18px; text-align: left; font-size: 1.08rem; font-weight: bold;">
                    Service
                    </th>
                    <th style="padding: 16px 18px; text-align: left; font-size: 1.08rem; font-weight: bold;">
                    Amount
                    </th>
                    <th style="padding: 16px 18px; text-align: left; font-size: 1.08rem; font-weight: bold; border-radius: 0 18px 0 0;">
                    Notes
                    </th>
                </tr>
                </thead>
				<tbody id="services-table-body">
					<!-- Services data will be populated from backend -->
					<tr style="background: #fff; border-bottom: 1px solid #f3e6f7;">
						<td colspan="3" style="padding: 40px 18px; text-align: center; color: #6b7280;">
							<div style="font-size: 16px; margin-bottom: 8px;">🏥</div>
							<p style="margin: 0; font-size: 14px;">No services found. Data will be loaded from backend.</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		</div>
	</div>

    <!-- Add Service Modal -->
    <div id="addServiceModal" class="modal-overlay">
        <div class="modal-content hospital-modal">
            <div class="modal-header hospital-header">
                <div class="header-content">
                    <div class="header-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 14C19 16.7614 16.7614 19 14 19H10C7.23858 19 5 16.7614 5 14V10C5 7.23858 7.23858 5 10 5H14C16.7614 5 19 7.23858 19 10V14Z" fill="currentColor"/>
                            <path d="M12 8V16M16 12H8" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="header-text">
                        <h2>Medical Service Registration</h2>
                        <p>Add new healthcare services to patient records</p>
                    </div>
                </div>
                <button type="button" class="modal-close hospital-close" id="closeAddServiceModal">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            
            <form id="addServiceForm" class="modal-form hospital-form">
                <div id="servicesContainer">
                    <!-- Service Entry 1 -->
                     <input type="hidden" id="patient_id_hidden" value="4">

                    <div class="service-entry hospital-service-entry">
                        <div class="form-grid">
                            <div class="form-group hospital-form-group">
                                <label for="service_date_1" class="hospital-label">
                                    <span class="label-icon">📅</span>
                                    Service Date
                                    <span class="required-mark">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <input type="date" id="service_date_1" name="service_date[]" class="hospital-input" required>
                                    <div class="input-border"></div>
                                </div>
                            </div>
                            
                            <div class="form-group hospital-form-group">
                                <label for="service_type_1" class="hospital-label">
                                    <span class="label-icon">🏥</span>
                                    Service Type
                                    <span class="required-mark">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <select id="service_type_1" name="service_type[]" class="hospital-select" required>
                                       
                                    </select>
                                    <div class="select-arrow">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group hospital-form-group">
                                <label for="service_doctor_1" class="hospital-label">
                                    <span class="label-icon">👨‍⚕️</span>
                                    Attending Physician
                                    <span class="required-mark">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <select id="service_doctor_1" name="service_doctor[]" class="hospital-select" required>
                                        <option value="">Select attending doctor</option>
                                    </select>
                                    <div class="select-arrow">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group hospital-form-group full-width">
                            <label for="service_notes_1" class="hospital-label">
                                <span class="label-icon">📝</span>
                                Clinical Notes
                                <span class="required-mark">*</span>
                            </label>
                            <div class="input-wrapper">
                                <textarea id="service_notes_1" name="service_notes[]" rows="4" class="hospital-textarea" placeholder="Enter detailed clinical observations, patient symptoms, and treatment recommendations..." required></textarea>
                                <div class="input-border"></div>
                            </div>
                        </div>
                        <div class="form-group hospital-form-group">
                        <label for="service_amount_1" class="hospital-label">
                            <span class="label-icon">💰</span>
                            Service Amount
                            <span class="required-mark">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="number" id="service_amount_1" name="service_amount[]" class="hospital-input" step="0.01" min="0" placeholder="Enter amount in PHP" required>
                            <div class="input-border"></div>
                        </div>
                        </div>

                    </div>
                </div>
                <script>
                function loadDoctorOptions() {
                fetch('http://localhost/JAM_Lyingin/auth/action/clerk/clerk_get_staff_list.php')
                    .then(response => response.json())
                    .then(data => {
                    const dropdown = document.getElementById('service_doctor_1');
                    dropdown.innerHTML = '<option value="">Select attending doctor</option>';

                    if (data.status === 'success') {
                        data.data.forEach(doctor => {
                        const option = document.createElement('option');
                        option.value = doctor.id; // or doctor.user_id if column is named that
                        option.textContent = doctor.full_name;
                        dropdown.appendChild(option);
                        });
                    } else {
                        const errorOption = document.createElement('option');
                        errorOption.value = '';
                        errorOption.textContent = 'Unable to load doctors';
                        dropdown.appendChild(errorOption);
                    }
                    })
                    .catch(error => {
                    console.error('Error loading doctors:', error);
                    });
                }
                
                // Load on page ready
                document.addEventListener('DOMContentLoaded', loadDoctorOptions);
                </script>
                <!-- Add More Service Button -->
                <div class="add-more-container hospital-add-more">
                    <button type="button" id="addMoreService" class="btn-add-more hospital-btn-add">
                        <div class="btn-icon">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span>Add Additional Service</span>
                    </button>
                </div>
                
                <!-- Action Buttons -->
                <div class="modal-actions hospital-actions">
                    <button type="button" id="cancelAddServiceModal" class="btn-cancel hospital-btn-cancel">
                        <span>Cancel</span>
                    </button>
                    <button type="submit" class="btn-save hospital-btn-save">
                        <span class="btn-icon">💾</span>
                        <span>Register Service</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
            <!-- Service Form Uploader Script po-->
    <script>
    document.getElementById('addServiceForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default form submission

    const form = e.target;
    const formData = new FormData();

    // Assuming patient_id is stored elsewhere (e.g., hidden input or global JS)
    const patientId = document.getElementById('patient_id_hidden')?.value || '4'; // Replace with actual source
    formData.append('patient_id', patientId);

    // Get first service entry (can be extended for multiple entries)
    const serviceDate = form.querySelector('[name="service_date[]"]').value;
    const serviceType = form.querySelector('[name="service_type[]"]').value;
    const doctorId = form.querySelector('[name="service_doctor[]"]').value;
    const notes = form.querySelector('[name="service_notes[]"]').value;
    const serviceAmount = form.querySelector('[name="service_amount[]"]').value;

    formData.append('service_date', serviceDate);
    formData.append('service_type', serviceType);
    formData.append('doctor_id', doctorId);
    formData.append('service_amount', serviceAmount);
    formData.append('notes', notes);

    fetch('http://localhost/JAM_Lyingin/auth/action/clerk/clerk_upload_service.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
        alert('Service registered successfully!');
        form.reset(); // Optional: clear form
        } else {
        alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Submission error:', err);
        alert('Failed to submit service.');
    });
    });
    </script>

    <script>
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
                    window.location.href = '/new lying-in/front.php';
                })
                .catch(error => {
                    console.error('Sign out error:', error);
                    // Still redirect even if server request fails
                    window.location.href = '/new lying-in/front.php';
                });
            }
        }








                 // Add Service button handlers for patient list
         document.addEventListener('DOMContentLoaded', function() {
             // Function to add approved appointments to the correct doctor's section
             function addAppointmentToDoctorSection(patientName, time, service, doctor) {
                 let targetSection;
                 if (!targetSection) {
                    console.warn(`Missing doctor section for: ${doctor}`);
                    return; // Skip rendering
                }

                 // Determine which doctor's section to add the appointment to
                 if (doctor === 'Dr. Maria Santos') {
                     targetSection = document.getElementById('dr-maria-santos-appointments');
                 } else if (doctor === 'Dr. Juan Rodriguez') {
                     targetSection = document.getElementById('dr-juan-rodriguez-appointments');
                 } else if (doctor === 'Dr. Ana Garcia') {
                     targetSection = document.getElementById('dr-ana-garcia-appointments');
                 } else {
                     // Default to Dr. Maria Santos if doctor not found
                     targetSection = document.getElementById('dr-maria-santos-appointments');
                 }
                 
                 if (targetSection) {
                     const tbody = targetSection.querySelector('tbody');
                     
                     // Check if there's a "No appointments today" row and remove it
                     const noAppointmentsRow = tbody.querySelector('tr td[colspan="5"]');
                     if (noAppointmentsRow) {
                         noAppointmentsRow.closest('tr').remove();
                     }
                     
                     // Create new appointment row
                     const newRow = document.createElement('tr');
                     newRow.style.borderBottom = '1px solid #eee';
                     
                     // Generate a simple patient ID (you might want to implement a proper ID system)
                     const patientId = Math.floor(Math.random() * 10000) + 1000;
                     
                     // Get today's date
                     const today = new Date().toLocaleDateString();
                     
                     newRow.innerHTML = `
                         <td style="padding: 10px 8px;">${patientId}</td>
                         <td style="padding: 10px 8px;">${patientName}</td>
                         <td style="padding: 10px 8px;">${today}</td>
                         <td style="padding: 10px 8px;">${service}</td>
                         <td style="padding: 10px 8px;">
                             <select name="status[]" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #ccc;">
                                 <option value="Waiting" selected>Waiting</option>
                                 <option value="Ongoing">Ongoing</option>
                                 <option value="Done">Done</option>
                             </select>
                         </td>
                     `;
                     
                     tbody.appendChild(newRow);
                 }
             }
             
             // Handle approve/reject appointment buttons
             document.addEventListener('click', function(e) {
                 if (e.target.classList.contains('btn-approve')) {
                     const appointmentCard = e.target.closest('.pending-appointment');
                     const patientName = appointmentCard.querySelector('span').textContent;
                     const time = appointmentCard.querySelector('span:last-of-type').textContent;
                     const service = appointmentCard.querySelector('p').textContent;
                     const doctorLine = appointmentCard.querySelector('p:last-of-type');
                     const doctor = doctorLine ? doctorLine.textContent.replace('Doctor: ', '').trim() : '';
                     
                     if (confirm(`Approve appointment for ${patientName} at ${time} with ${doctor}?`)) {
                         // Move appointment to the correct doctor's section
                         if (doctor) {
                             addAppointmentToDoctorSection(patientName, time, service, doctor);
                         }
                         
                         // Remove the approved appointment from pending
                         appointmentCard.remove();
                     }
                 }
                 

                 
                 if (e.target.classList.contains('btn-edit')) {
                     const appointmentCard = e.target.closest('.pending-appointment');
                     const patientName = appointmentCard.querySelector('span').textContent;
                     const time = appointmentCard.querySelector('span:last-of-type').textContent;
                     const service = appointmentCard.querySelector('p').textContent;
                     
                     // Store reference to the appointment card for later use
                     window.currentEditingCard = appointmentCard;
                     window.originalAppointmentData = { patientName, time, service };
                     
                     // Populate modal fields
                     document.getElementById('editPatientName').value = patientName;
                     
                     // Convert time format from "9:00 AM" to "09:00" for time input
                     let timeValue = time;
                     if (time.includes('AM') || time.includes('PM')) {
                         const timeMatch = time.match(/(\d+):(\d+)\s*(AM|PM)/);
                         if (timeMatch) {
                             let hours = parseInt(timeMatch[1]);
                             const minutes = timeMatch[2];
                             const period = timeMatch[3];
                             
                             if (period === 'PM' && hours !== 12) {
                                 hours += 12;
                             } else if (period === 'AM' && hours === 12) {
                                 hours = 0;
                             }
                             
                             timeValue = `${hours.toString().padStart(2, '0')}:${minutes}`;
                         }
                     }
                     document.getElementById('editAppointmentTime').value = timeValue;
                     
                     document.getElementById('editServiceType').value = service;
                     
                     // Set default doctor based on service type or keep existing
                     const doctorSelect = document.getElementById('editSelectedDoctor');
                     if (service === 'Prenatal Check-up') {
                         doctorSelect.value = 'Dr. Maria Santos';
                     } else if (service === 'Ultrasound') {
                         doctorSelect.value = 'Dr. Juan Rodriguez';
                     } else if (service === 'Blood Test') {
                         doctorSelect.value = 'Dr. Ana Garcia';
                     } else {
                         doctorSelect.value = 'Dr. Maria Santos'; // Default doctor
                     }
                     
                     document.getElementById('editNotes').value = '';
                     
                     // Show modal with animation
                     const modal = document.getElementById('editAppointmentModal');
                     modal.style.display = 'flex';
                     setTimeout(() => {
                         modal.classList.add('show');
                     }, 10);
                 }
             });
             
        // Add some basic interactivity
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('.card-title').textContent;
                alert(`Opening ${title} module...`);
            });
        });

            // Navigation functionality
document.querySelectorAll('.nav-item').forEach(item => {
  item.addEventListener('click', function(e) {
    e.preventDefault();

    // Reset active state
    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
    this.classList.add('active');

    // Section references
    const mainContent = document.querySelector('.main-content > .content-section');
    const patientList = document.getElementById('patient-list-section');
    const servicesSection = document.getElementById('services-section');
    const billingDashboard = document.getElementById('billingDashboard');
    const settingsSection = document.getElementById('settings-section');
    // Hide all sections first
    mainContent.style.display = 'none';
    patientList.style.display = 'none';
    servicesSection.style.display = 'none';
    billingDashboard.style.display = 'none';
    settingsSection.style.display = 'none';
    // Show selected section
    const label = this.textContent.trim();
    if (label.includes('Patients')) {
      patientList.style.display = '';
    } else if (label.includes('Services')) {
      servicesSection.style.display = '';
    } else if (label.includes('Home')) {
      mainContent.style.display = '';
    } else if (label.includes('Billing')) {
      billingDashboard.style.display = 'flex'; // Use flex to activate centering
    }
    else if (label.includes('Settings')) {
      settingsSection.style.display = '';
    }
  });
});


            // Sign out functionality
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
            if (document.getElementById('patient-search')) {
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
            }

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

            
            // Handle "Add Service" clicks from patient list
            const addServiceLinks = document.querySelectorAll('a[onclick*="services-section"]');
            addServiceLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Hide all sections
                    document.querySelector('.main-content > .content-section').style.display = 'none';
                    document.getElementById('patient-list-section').style.display = 'none';
                    document.getElementById('services-section').style.display = 'block';
                    
                    // Update navigation active state
                    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                    Array.from(document.querySelectorAll('.nav-item')).find(nav => nav.textContent.trim().includes('Services'))?.classList.add('active');
                });
            });

            // Add Service Modal functionality
            const openAddServiceModal = document.getElementById('openAddServiceModal');
            const addServiceModal = document.getElementById('addServiceModal');
            const closeAddServiceModal = document.getElementById('closeAddServiceModal');
            const cancelAddServiceModal = document.getElementById('cancelAddServiceModal');
            const addMoreServiceBtn = document.getElementById('addMoreService');
            const servicesContainer = document.getElementById('servicesContainer');
            const addServiceForm = document.getElementById('addServiceForm');

            // Open modal
            openAddServiceModal.addEventListener('click', function() {
                console.log('Add Service button clicked');
                console.log('Modal element:', addServiceModal);
                addServiceModal.style.display = 'flex';
                console.log('Modal display set to flex');
                setTimeout(() => {
                    addServiceModal.classList.add('show');
                    console.log('Show class added');
                }, 10);
            });

            // Close modal (X button)
            closeAddServiceModal.addEventListener('click', function() {
                addServiceModal.classList.remove('show');
                setTimeout(() => {
                    addServiceModal.style.display = 'none';
                }, 300);
            });

            // Cancel modal (Cancel button)
            cancelAddServiceModal.addEventListener('click', function() {
                addServiceModal.classList.remove('show');
                setTimeout(() => {
                    addServiceModal.style.display = 'none';
                }, 300);
            });

            // Close modal when clicking outside
            addServiceModal.addEventListener('click', function(e) {
                if (e.target === addServiceModal) {
                    addServiceModal.classList.remove('show');
                    setTimeout(() => {
                        addServiceModal.style.display = 'none';
                    }, 300);
                }
            });
            
            // Close modal with escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && addServiceModal.style.display === 'flex') {
                    addServiceModal.classList.remove('show');
                    setTimeout(() => {
                        addServiceModal.style.display = 'none';
                    }, 300);
                }
            });

            // Function to auto-populate doctor based on service type
            function autoPopulateDoctor(serviceSelect, doctorSelect) {
                const service = serviceSelect.value;
                if (service === 'Pre-Natal Check Up' || service === 'Prenatal Check-up') {
                    doctorSelect.value = 'Dr. Maria Santos';
                } else if (service === 'Ultrasound') {
                    doctorSelect.value = 'Dr. Juan Rodriguez';
                } else if (service === 'Blood Test') {
                    doctorSelect.value = 'Dr. Ana Garcia';
                } else if (service === 'Normal Delivery' || service === 'Post Partum Check Up') {
                    doctorSelect.value = 'Dr. Maria Santos';
                } else if (service === 'Pedia Check Up' || service === 'Immunization' || service === 'BCG/HEPA-B Vaccine') {
                    doctorSelect.value = 'Dr. Ana Garcia';
                } else {
                    doctorSelect.value = 'Dr. Maria Santos'; // Default doctor
                }
            }

            // Add event listeners for auto-populating doctor fields
            document.addEventListener('change', function(e) {
                if (e.target.name === 'service_type[]') {
                    const serviceEntry = e.target.closest('.service-entry');
                    const doctorSelect = serviceEntry.querySelector('select[name="service_doctor[]"]');
                    if (doctorSelect) {
                        autoPopulateDoctor(e.target, doctorSelect);
                    }
                }
            });

            // Initialize auto-population for the first service entry
            const firstServiceType = document.getElementById('service_type_1');
            const firstDoctorSelect = document.getElementById('service_doctor_1');
            if (firstServiceType && firstDoctorSelect) {
                firstServiceType.addEventListener('change', function() {
                    autoPopulateDoctor(this, firstDoctorSelect);
                });
            }

            // Add more service entries
            addMoreServiceBtn.addEventListener('click', function() {
                const serviceCount = document.querySelectorAll('.service-entry').length + 1;
                const serviceEntry = document.createElement('div');
                serviceEntry.className = 'service-entry hospital-service-entry';
                
                serviceEntry.innerHTML = `
                    <div class="form-grid">
                        <div class="form-group hospital-form-group">
                            <label for="service_date_${serviceCount}" class="hospital-label">
                                <span class="label-icon">📅</span>
                                Service Date
                                <span class="required-mark">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="date" id="service_date_${serviceCount}" name="service_date[]" class="hospital-input" required>
                                <div class="input-border"></div>
                            </div>
                        </div>
                        
                        <div class="form-group hospital-form-group">
                            <label for="service_type_${serviceCount}" class="hospital-label">
                                <span class="label-icon">🏥</span>
                                Service Type
                                <span class="required-mark">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select id="service_type_${serviceCount}" name="service_type[]" class="hospital-select" required>
                                    <option value="">Choose service category</option>
                                    <option value="Pre-Natal Check Up">Pre-Natal Check Up</option>
                                    <option value="Normal Delivery">Normal Delivery</option>
                                    <option value="Pedia Check Up">Pedia Check Up</option>
                                    <option value="Papsmear">Papsmear</option>
                                    <option value="Family Planning">Family Planning</option>
                                    <option value="BCG/HEPA-B Vaccine">BCG/HEPA-B Vaccine</option>
                                    <option value="Newborn Screening">Newborn Screening</option>
                                    <option value="Hearing Test">Hearing Test</option>
                                    <option value="Post Partum Check Up">Post Partum Check Up</option>
                                    <option value="Immunization">Immunization</option>
                                </select>
                                <div class="select-arrow">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group hospital-form-group">
                            <label for="service_doctor_${serviceCount}" class="hospital-label">
                                <span class="label-icon">👨‍⚕️</span>
                                Attending Physician
                                <span class="required-mark">*</span>
                            </label>
                            <div class="input-wrapper">
                                <select id="service_doctor_${serviceCount}" name="service_doctor[]" class="hospital-select" required>
                                    <option value="">Select attending doctor</option>
                                    <option value="Dr. Maria Santos">Dr. Maria Santos - OB-GYN Specialist</option>
                                    <option value="Dr. Juan Rodriguez">Dr. Juan Rodriguez - Ultrasound Specialist</option>
                                    <option value="Dr. Ana Garcia">Dr. Ana Garcia - Laboratory Medicine</option>
                                    <option value="Dr. Carlos Lopez">Dr. Carlos Lopez - Pediatrics</option>
                                    <option value="Dr. Sofia Martinez">Dr. Sofia Martinez - Family Medicine</option>
                                    <option value="Dr. Miguel Torres">Dr. Miguel Torres - Emergency Medicine</option>
                                </select>
                                <div class="select-arrow">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group hospital-form-group full-width">
                        <label for="service_notes_${serviceCount}" class="hospital-label">
                            <span class="label-icon">📝</span>
                            Clinical Notes
                            <span class="required-mark">*</span>
                        </label>
                        <div class="input-wrapper">
                            <textarea id="service_notes_${serviceCount}" name="service_notes[]" rows="4" class="hospital-textarea" placeholder="Enter detailed clinical observations, patient symptoms, and treatment recommendations..." required></textarea>
                            <div class="input-border"></div>
                        </div>
                    </div>
                    
                    <div class="service-actions">
                        <button type="button" class="remove-service hospital-remove-btn">
                            <span class="btn-icon">🗑️</span>
                            <span>Remove Service</span>
                        </button>
                    </div>
                `;

                // Add remove functionality for new entries
                const removeBtn = serviceEntry.querySelector('.remove-service');
                removeBtn.addEventListener('click', function() {
                    serviceEntry.remove();
                });

                servicesContainer.appendChild(serviceEntry);
            });

            // Handle form submission
            addServiceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Collect all service entries
                const serviceEntries = [];
                const dateInputs = document.querySelectorAll('input[name="service_date[]"]');
                const serviceInputs = document.querySelectorAll('select[name="service_type[]"]');
                const doctorInputs = document.querySelectorAll('select[name="service_doctor[]"]');
                const notesInputs = document.querySelectorAll('textarea[name="service_notes[]"]');
                
                for (let i = 0; i < dateInputs.length; i++) {
                    if (dateInputs[i].value && serviceInputs[i].value && doctorInputs[i].value) {
                        serviceEntries.push({
                            date: dateInputs[i].value,
                            service: serviceInputs[i].value,
                            doctor: doctorInputs[i].value,
                            notes: notesInputs[i].value || ''
                        });
                    }
                }
                
                if (serviceEntries.length > 0) {
                    alert(`Successfully added ${serviceEntries.length} service(s)!`);
                    console.log('Service entries:', serviceEntries);
                    
                    // Here you would typically send the data to your server
                    // For now, we'll just close the modal
                    addServiceModal.style.display = 'none';
                    
                    // Reset the form
                    addServiceForm.reset();
                    
                    // Remove all additional service entries except the first one
                    const entries = servicesContainer.querySelectorAll('.service-entry');
                    for (let i = 1; i < entries.length; i++) {
                        entries[i].remove();
                    }
                } else {
                    alert('Please fill in at least one service entry.');
                }
            });

            // Edit Appointment Modal functionality
            const editAppointmentModal = document.getElementById('editAppointmentModal');
            const closeEditModal = document.getElementById('closeEditModal');
            const cancelEdit = document.getElementById('cancelEdit');
            const editAppointmentForm = document.getElementById('editAppointmentForm');

            // Close modal
            closeEditModal.addEventListener('click', function() {
                editAppointmentModal.classList.remove('show');
                setTimeout(() => {
                    editAppointmentModal.style.display = 'none';
                }, 300);
            });

            // Cancel edit
            cancelEdit.addEventListener('click', function() {
                editAppointmentModal.classList.remove('show');
                setTimeout(() => {
                    editAppointmentModal.style.display = 'none';
                }, 300);
            });

            // Close modal when clicking outside
            editAppointmentModal.addEventListener('click', function(e) {
                if (e.target === editAppointmentModal) {
                    editAppointmentModal.classList.remove('show');
                    setTimeout(() => {
                        editAppointmentModal.style.display = 'none';
                    }, 300);
                }
            });

            // Handle form submission
            editAppointmentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const newName = document.getElementById('editPatientName').value;
                const newTime = document.getElementById('editAppointmentTime').value;
                const newService = document.getElementById('editServiceType').value;
                const newDoctor = document.getElementById('editSelectedDoctor').value;
                const notes = document.getElementById('editNotes').value;
                
                if (newName && newTime && newService && newDoctor) {
                    // Convert time format from "09:00" to "9:00 AM" for display
                    let displayTime = newTime;
                    if (newTime.match(/^\d{2}:\d{2}$/)) {
                        const [hours, minutes] = newTime.split(':');
                        const hour = parseInt(hours);
                        const period = hour >= 12 ? 'PM' : 'AM';
                        const displayHour = hour === 0 ? 12 : (hour > 12 ? hour - 12 : hour);
                        displayTime = `${displayHour}:${minutes} ${period}`;
                    }
                    
                    // Update the appointment card
                    if (window.currentEditingCard) {
                        window.currentEditingCard.innerHTML = `
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-weight: 500; color: #6c336e; font-size: 14px;">${newName}</span>
                                <span style="font-size: 12px; color: #666;">${displayTime}</span>
                            </div>
                            <p style="font-size: 12px; color: #666; margin-bottom: 8px;">${newService}</p>
                            <p style="font-size: 12px; color: #666; margin-bottom: 8px;"><strong>Doctor:</strong> ${newDoctor}</p>
                            ${notes ? `<p style="font-size: 11px; color: #888; margin-bottom: 8px; font-style: italic;">Notes: ${notes}</p>` : ''}
                            <div style="display: flex; gap: 6px;">
                                <button class="btn-approve" style="background: linear-gradient(90deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; transition: all 0.2s ease;">Approve</button>
                                <button class="btn-edit" style="background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; transition: all 0.2s ease;">Edit</button>
                            </div>
                        `;
                    }
                    
                    // Close modal with animation
                    editAppointmentModal.classList.remove('show');
                    setTimeout(() => {
                        editAppointmentModal.style.display = 'none';
                    }, 300);
                    
                    alert('Appointment updated successfully!');
                } else {
                    alert('Please fill in all required fields.');
                }
            });

            // Services Page Functionality
            // Function to populate services page with patient data
            function populateServicesPage(patientName, patientId) {
                // Hide blank state and show content
                document.getElementById('services-blank-state').style.display = 'none';
                document.getElementById('services-content').style.display = 'block';
                
                // Update patient information
                document.getElementById('services-patient-name').textContent = patientName;
                document.getElementById('services-patient-id').textContent = `Patient ID: ${patientId}`;
                
                // Load patient services from backend
                loadPatientServicesFromBackend(patientId);
            }

            // Function to add a service to the services table
            function addServiceToTable(serviceDate, serviceType, notes, doctor) {
                const tbody = document.getElementById('services-table-body');
                
                // Create new service row
                const newRow = document.createElement('tr');
                newRow.style.borderBottom = '1px solid #f3e6f7';
                
                // Alternate background colors
                const rowCount = tbody.children.length;
                newRow.style.background = rowCount % 2 === 0 ? '#fff' : '#faf6fa';
                
                newRow.innerHTML = `
                    <td style="padding: 14px 18px; color: #6c336e; font-weight: 500;">${serviceDate}</td>
                    <td style="padding: 14px 18px; font-weight: 500;">${serviceType}</td>
                    <td style="padding: 14px 18px; color: #666;">${notes || 'No additional notes'}</td>
                `;
                
                tbody.appendChild(newRow);
            }

            // Function to reset services page to blank state
            function resetServicesPage() {
                document.getElementById('services-blank-state').style.display = 'block';
                document.getElementById('services-content').style.display = 'none';
                
                // Clear patient info
                document.getElementById('services-patient-name').textContent = 'Patient Name';
                document.getElementById('services-patient-id').textContent = 'Patient ID: PAT-0000-000';
                
                // Clear services table
                document.getElementById('services-table-body').innerHTML = '';
                
                // Clear search and hide results for services content
                const searchInput = document.getElementById('services-search');
                if (searchInput) {
                    searchInput.value = '';
                }
                document.getElementById('search-results').style.display = 'none';
                
                // Clear search and hide results for blank state
                const searchInputBlank = document.getElementById('services-search-blank');
                if (searchInputBlank) {
                    searchInputBlank.value = '';
                }
                document.getElementById('search-results-blank').style.display = 'none';
            }

            // Function to go back to patient list
            function goBackToPatientList() {
                document.getElementById('services-section').style.display = 'none';
                document.getElementById('patient-list-section').style.display = 'block';
                
                // Update navigation
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                Array.from(document.querySelectorAll('.nav-item')).find(nav => nav.textContent.trim().includes('Patient List'))?.classList.add('active');
                
                // Reset services page to blank state
                resetServicesPage();
            }

            // Patient data will be loaded from backend
            let patientsData = [];

            // Function to load patients from backend

// Function to load all patients from backend at runstart
async function loadPatientsFromBackend() {
    try {
        const response = await fetch('auth/action/clerk/clerk_get_patients.php');
        const data = await response.json();

        if (data.status === 'success') {
patientsData = data.data.map(p => ({
    ...p,
    id: p.patient_id,
    name: `${p.first_name} ${p.last_name}`,
    status: p.status_label
}));

            console.log('Loaded patients:', patientsData);

            // Populate patient table or search results
            const resultsContainer = document.getElementById('search-results-blank');
            const resultsContent = document.getElementById('search-results-content-blank');
            resultsContent.innerHTML = '';

            patientsData.forEach(patient => {
                const div = document.createElement('div');
                div.style.padding = '12px 16px';
                div.style.borderBottom = '1px solid #eee';
                div.style.cursor = 'pointer';
                div.textContent = `${patient.patient_id} - ${patient.first_name} ${patient.last_name} (${patient.status_label})`;
                div.onclick = () => {
                    console.log("Selected patient:", patient);
                    // TODO: load patient services view
                };
                resultsContent.appendChild(div);
            });

            resultsContainer.style.display = 'block';
        } else {
            console.warn(data.message || 'No patients found');
        }
    } catch (error) {
        console.error('Error loading patients:', error);
    }
}

// 🔑 Call it once when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    loadPatientsFromBackend(); // fetch all patients immediately
});
            // Function to populate patient table from backend data


            // Function to load services for a specific patient from backend
async function loadPatientServicesFromBackend(patientId) {
    try {
        const response = await fetch(
            `auth/action/clerk/clerk_get_patient_services.php?patient_id=${encodeURIComponent(patientId)}`
        );
        const data = await response.json();

        const tbody = document.getElementById('services-table-body');
        if (!tbody) return;

        tbody.innerHTML = ''; // clear existing rows

        if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
            data.data.forEach(service => {
                const tr = document.createElement('tr');
                tr.style.background = '#fff';
                tr.style.borderBottom = '1px solid #f3e6f7';

                tr.innerHTML = `
                    <td style="padding: 12px 18px; color: #374151;">${service.service_date}</td>
                    <td style="padding: 12px 18px; color: #374151;">${service.service_type}</td>
                    <td style="padding: 12px 18px; color: #374151;">${service.service_amount}</td>
                    <td style="padding: 12px 18px; color: #374151;">${service.notes || ''}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `
                <tr style="background: #fff; border-bottom: 1px solid #f3e6f7;">
                    <td colspan="4" style="padding: 40px 18px; text-align: center; color: #6b7280;">
                        <div style="font-size: 16px; margin-bottom: 8px;">🏥</div>
                        <p style="margin: 0; font-size: 14px;">No services found for this patient.</p>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading patient services:', error);
    }
}

            // Function to load pending appointments from backend
            async function loadPendingAppointmentsFromBackend() {
                try {
                    // This will be replaced with actual backend API call
                    // const response = await fetch('/api/appointments/pending');
                    // const appointmentsData = await response.json();
                    
                    // For now, show message that data will come from backend
                    console.log('Pending appointments data will be loaded from backend');
                    
                    // Populate pending appointments container
                    populatePendingAppointments();
                } catch (error) {
                    console.error('Error loading pending appointments:', error);
                }
            }

            // Function to populate pending appointments from backend data
            function populatePendingAppointments() {
                const container = document.getElementById('pending-appointments-container');
                if (!container) return;
                
                // For now, show placeholder message since no backend data
                // This will be replaced when backend data is available
                container.innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #6b7280;">
                        <div style="font-size: 16px; margin-bottom: 8px;">📅</div>
                        <p style="margin: 0; font-size: 14px;">No pending appointments found. Data will be loaded from backend.</p>
                    </div>
                `;
            }

            // Function to create appointment card HTML (for when backend data is available)
            function createAppointmentCard(appointment) {
                return `
                    <div class="pending-appointment" style="background: #f8f9fe; border-radius: 10px; padding: 12px; margin-bottom: 10px; border-left: 4px solid #ff6bcb;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-weight: 500; color: #6c336e; font-size: 14px;">${appointment.patientName}</span>
                            <span style="font-size: 12px; color: #666;">${appointment.time}</span>
                        </div>
                        <p style="font-size: 12px; color: #666; margin-bottom: 8px;">${appointment.service}</p>
                        <p style="font-size: 12px; color: #666; margin-bottom: 8px;"><strong>Doctor:</strong> ${appointment.doctor}</p>
                        <div style="display: flex; gap: 6px;">
                            <button class="btn-approve" style="background: linear-gradient(90deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; transition: all 0.2s ease;">Approve</button>
                            <button class="btn-edit" style="background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; transition: all 0.2s ease;">Edit</button>
                        </div>
                    </div>
                `;
            }

            // Function to refresh pending appointments (useful for real-time updates)
            async function refreshPendingAppointments() {
                try {
                    // This will be replaced with actual backend API call
                    // const response = await fetch('/api/appointments/pending');
                    // const appointmentsData = await response.json();
                    
                    console.log('Refreshing pending appointments from backend');
                    
                    // For now, just reload the existing function
                    // When backend is ready, this will populate with real data
                    populatePendingAppointments();
                } catch (error) {
                    console.error('Error refreshing pending appointments:', error);
                }
            }

            // Function to handle appointment approval (will integrate with backend)
            async function approveAppointment(appointmentId) {
                try {
                    // This will be replaced with actual backend API call
                    // const response = await fetch(`/api/appointments/${appointmentId}/approve`, {
                    //     method: 'POST',
                    //     headers: { 'Content-Type': 'application/json' }
                    // });
                    
                    console.log(`Approving appointment ${appointmentId} via backend`);
                    
                    // After successful approval, refresh the pending appointments
                    // refreshPendingAppointments();
                    
                    // Show success message
                    alert('Appointment approved successfully!');
                } catch (error) {
                    console.error('Error approving appointment:', error);
                    alert('Error approving appointment. Please try again.');
                }
            }

            // Function to search patients
            function searchPatients() {
                const searchTerm = document.getElementById('services-search').value.toLowerCase().trim();
                const searchResults = document.getElementById('search-results');
                const searchResultsContent = document.getElementById('search-results-content');
                
                if (searchTerm === '') {
                    searchResults.style.display = 'none';
                    return;
                }
                
                // Check if patients data is loaded
                if (patientsData.length === 0) {
                    searchResults.style.display = 'block';
                    searchResultsContent.innerHTML = `
                        <div style="padding: 20px; text-align: center; color: #6b7280;">
                            <div style="font-size: 24px; margin-bottom: 8px;">📋</div>
                            <p style="margin: 0; font-size: 14px;">No patients data available. Please wait for backend data to load.</p>
                        </div>
                    `;
                    return;
                }
                
                // Filter patients based on search term
const filteredPatients = patientsData.filter(patient =>
    (`${patient.first_name} ${patient.last_name}`).toLowerCase().includes(searchTerm) ||
    patient.patient_id.toString().toLowerCase().includes(searchTerm)
);



                
                if (filteredPatients.length === 0) {
                    searchResults.style.display = 'block';
                    searchResultsContent.innerHTML = `
                        <div style="padding: 20px; text-align: center; color: #6b7280;">
                            <div style="font-size: 24px; margin-bottom: 8px;">🔍</div>
                            <p style="margin: 0; font-size: 14px;">No patients found matching "${searchTerm}"</p>
                        </div>
                    `;
                    return;
                }
                
                // Display search results
                searchResults.style.display = 'block';
                searchResultsContent.innerHTML = filteredPatients.map(patient => `
                    <div style="padding: 16px 20px; border-bottom: 1px solid #f3e6f7; cursor: pointer; transition: background-color 0.2s ease;" 
                         onmouseover="this.style.backgroundColor='#f9fafb'" 
                         onmouseout="this.style.backgroundColor='white'"
                         onclick="selectPatient('${patient.id}', '${patient.name}')">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600; color: #374151; margin-bottom: 4px;">${patient.name}</div>
                                <div style="font-size: 13px; color: #6b7280;">ID: ${patient.id} • ${patient.age} years • ${patient.gender}</div>
                            </div>
                            <div style="color: #10b981; font-size: 12px; font-weight: 500; background: #d1fae5; padding: 4px 8px; border-radius: 6px;">
                                ${patient.status}
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            // Function to select a patient from search results
            function selectPatient(patientId, patientName) {
                // Hide search results
                document.getElementById('search-results').style.display = 'none';
                
                // Clear search input
                document.getElementById('services-search').value = '';
                
                // Populate services page with selected patient
                populateServicesPage(patientName, patientId);
            }

            // Function to search patients from blank state
            function searchPatientsFromBlank() {
                const searchTerm = document.getElementById('services-search-blank').value.toLowerCase().trim();
                const searchResults = document.getElementById('search-results-blank');
                const searchResultsContent = document.getElementById('search-results-content-blank');
                
                if (searchTerm === '') {
                    searchResults.style.display = 'none';
                    return;
                }
                
                // Filter patients based on search term
const filteredPatients = patientsData.filter(patient =>
    (`${patient.first_name} ${patient.last_name}`).toLowerCase().includes(searchTerm) ||
    patient.patient_id.toString().toLowerCase().includes(searchTerm)
);

                
                if (filteredPatients.length === 0) {
                    searchResults.style.display = 'block';
                    searchResultsContent.innerHTML = `
                        <div style="padding: 20px; text-align: center; color: #6b7280;">
                            <div style="font-size: 24px; margin-bottom: 8px;">🔍</div>
                            <p style="margin: 0; font-size: 14px;">No patients found matching "${searchTerm}"</p>
                        </div>
                    `;
                    return;
                }
                
                // Display search results
                searchResults.style.display = 'block';
                searchResultsContent.innerHTML = filteredPatients.map(patient => `
                    <div style="padding: 16px 20px; border-bottom: 1px solid #f3e6f7; cursor: pointer; transition: background-color 0.2s ease;" 
                         onmouseover="this.style.backgroundColor='#f9fafb'" 
                         onmouseout="this.style.backgroundColor='white'"
                         onclick="selectPatientFromBlank('${patient.id}', '${patient.name}')">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600; color: #374151; margin-bottom: 4px;">${patient.name}</div>
                                <div style="font-size: 13px; color: #6b7280;">ID: ${patient.id} • ${patient.age} years • ${patient.gender}</div>
                            </div>
                            <div style="color: #10b981; font-size: 12px; font-weight: 500; background: #d1fae5; padding: 4px 8px; border-radius: 6px;">
                                ${patient.status}
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            // Function to select a patient from blank state search results
            function selectPatientFromBlank(patientId, patientName) {
                // Hide search results
                document.getElementById('search-results-blank').style.display = 'none';
                
                // Clear search input
                document.getElementById('services-search-blank').value = '';
                
                // Populate services page with selected patient
                populateServicesPage(patientName, patientId);
            }



            // Make functions globally accessible for use from Patient List page
            window.populateServicesPage = populateServicesPage;
            window.addServiceToTable = addServiceToTable;
            window.resetServicesPage = resetServicesPage;
            window.goBackToPatientList = goBackToPatientList;
            window.searchPatients = searchPatients;
            window.selectPatient = selectPatient;
            window.searchPatientsFromBlank = searchPatientsFromBlank;
            window.selectPatientFromBlank = selectPatientFromBlank;
            window.loadPatientsFromBackend = loadPatientsFromBackend;
            
            window.loadPatientServicesFromBackend = loadPatientServicesFromBackend;
            window.loadPendingAppointmentsFromBackend = loadPendingAppointmentsFromBackend;
            window.populatePendingAppointments = populatePendingAppointments;
            window.createAppointmentCard = createAppointmentCard;
            window.refreshPendingAppointments = refreshPendingAppointments;
            window.approveAppointment = approveAppointment;
        });
                    // Event listeners for services page
            document.addEventListener('DOMContentLoaded', function() {
                // Load patients data from backend when page loads
                loadPatientsFromBackend();
                
                // Load pending appointments from backend when page loads
                loadPendingAppointmentsFromBackend();
                
                // Handle "Add Service" button from blank state
                const openAddServiceModal = document.getElementById('openAddServiceModal');
                if (openAddServiceModal) {
                    openAddServiceModal.addEventListener('click', function() {
                        // Show the add service modal
                        document.getElementById('addServiceModal').style.display = 'flex';
                    });
                }

                // Handle search input events for services content
                const servicesSearch = document.getElementById('services-search');
                if (servicesSearch) {
                    // Add debounced search for better performance
                    let searchTimeout;
                    servicesSearch.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => {
                            searchPatients();
                        }, 300);
                    });

                    // Handle search input focus
                    servicesSearch.addEventListener('focus', function() {
                        this.style.borderColor = '#667eea';
                        this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
                    });

                    // Handle search input blur
                    servicesSearch.addEventListener('blur', function() {
                        this.style.borderColor = '#e5e7eb';
                        this.style.boxShadow = '0 2px 8px rgba(108,51,110,0.04)';
                    });

                    // Handle escape key to clear search
                    servicesSearch.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            this.value = '';
                            document.getElementById('search-results').style.display = 'none';
                        }
                    });

                    // Handle clicking outside search results to hide them
                    document.addEventListener('click', function(e) {
                        const searchResults = document.getElementById('search-results');
                        const searchInput = document.getElementById('services-search');
                        
                        if (searchResults && searchInput && 
                            !searchResults.contains(e.target) && 
                            !searchInput.contains(e.target)) {
                            searchResults.style.display = 'none';
                        }
                    });
                }

                // Handle search input events for blank state
                const servicesSearchBlank = document.getElementById('services-search-blank');
                if (servicesSearchBlank) {
                    // Add debounced search for better performance
                    let searchTimeoutBlank;
                    servicesSearchBlank.addEventListener('input', function() {
                        clearTimeout(searchTimeoutBlank);
                        searchTimeoutBlank = setTimeout(() => {
                            searchPatientsFromBlank();
                        }, 300);
                    });

                    // Handle search input focus
                    servicesSearchBlank.addEventListener('focus', function() {
                        this.style.borderColor = '#667eea';
                        this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
                    });

                    // Handle search input blur
                    servicesSearchBlank.addEventListener('blur', function() {
                        this.style.borderColor = '#e5e7eb';
                        this.style.boxShadow = '0 4px 16px rgba(108,51,110,0.08)';
                    });

                    // Handle escape key to clear search
                    servicesSearchBlank.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            this.value = '';
                            document.getElementById('search-results-blank').style.display = 'none';
                        }
                    });

                    // Handle clicking outside search results to hide them
                    document.addEventListener('click', function(e) {
                        const searchResults = document.getElementById('search-results-blank');
                        const searchInput = document.getElementById('services-search-blank');
                        
                        if (searchResults && searchInput && 
                            !searchResults.contains(e.target) && 
                            !searchInput.contains(e.target)) {
                            searchResults.style.display = 'none';
                        }
                    });
                }

                // Handle "Add Service" button from services content
                const openAddServiceModalFromServices = document.getElementById('openAddServiceModalFromServices');
                if (openAddServiceModalFromServices) {
                    openAddServiceModalFromServices.addEventListener('click', function() {
                        // Show the add service modal
                        document.getElementById('addServiceModal').style.display = 'flex';
                    });
                }

                // Handle form submission to add services
                const addServiceForm = document.getElementById('addServiceForm');
                if (addServiceForm) {
                    addServiceForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const serviceEntries = [];
                        const entries = document.querySelectorAll('.service-entry');
                        
                        entries.forEach((entry, index) => {
                            const date = entry.querySelector('input[name="service_date[]"]').value;
                            const type = entry.querySelector('select[name="service_type[]"]').value;
                            const doctor = entry.querySelector('select[name="doctor[]"]').value;
                            const notes = entry.querySelector('textarea[name="notes[]"]').value;
                            
                            if (date && type && doctor) {
                                serviceEntries.push({
                                    date: date,
                                    type: type,
                                    doctor: doctor,
                                    notes: notes
                                });
                            }
                        });
                        
                        if (serviceEntries.length > 0) {
                            // Add services to the table
                            serviceEntries.forEach(service => {
                                addServiceToTable(service.date, service.type, service.notes, service.doctor);
                            });
                            
                            // Close modal
                            document.getElementById('addServiceModal').style.display = 'none';
                            
                            // Reset form
                            addServiceForm.reset();
                            
                            // Remove additional service entries
                            const additionalEntries = document.querySelectorAll('.service-entry:not(:first-child)');
                            additionalEntries.forEach(entry => entry.remove());
                            
                            alert('Services added successfully!');
                        } else {
                            alert('Please fill in at least one service entry.');
                        }
                    });
                }
            });
    </script>
</body>
</html>
