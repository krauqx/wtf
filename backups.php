<?php

session_start();
if (!isset($_SESSION['doctor_name'])) {
    $_SESSION['doctor_name'] = 'Doctor Gregorio';
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JAM Lying-in Clinic - Dashboard</title>
    <style>
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

        .doctor-info {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .doctor-avatar {
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

        .doctor-name {
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
            padding: 30px 30px 30px 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            box-sizing: border-box;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 50px;
            align-items: start;
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

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(44, 62, 80, 0.18);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        /* Ensure modal displays when set to flex */
        .modal-overlay[style*="display: flex"] {
            display: flex !important;
            z-index: 99999 !important;
        }

        .modal-content {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(108, 51, 110, 0.18);
            overflow: hidden;
            max-width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
        }
        /* Services Page CSS Styles */
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

        /* Hospital Modal Styles */
        .hospital-modal {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: min(95vw, 900px);
            max-height: min(95vh, 800px);
        }

        .hospital-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 20px 20px 0 0;
            padding: 28px 32px 24px 32px;
            position: relative;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-icon {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-text h2 {
            color: white;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .header-text p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin: 0;
        }

        .hospital-close {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px;
            color: white;
            cursor: pointer;
            position: absolute;
            top: 24px;
            right: 24px;
        }

        .hospital-form {
            padding: 32px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .hospital-service-entry {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-bottom: 32px;
        }

        .hospital-form-group {
            margin-bottom: 8px;
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
        }

        .label-icon {
            font-size: 16px;
        }

        .required-mark {
            color: #ef4444;
            font-weight: 700;
            margin-left: 4px;
        }

        .input-wrapper {
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

        .hospital-textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

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
            min-width: 140px;
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
            min-width: 180px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        }

        /* Additional Services CSS */
        .service-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
        }

        .remove-service {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 2px solid #fecaca;
            color: #dc2626;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-service:hover {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #f87171;
            transform: translateY(-1px);
        }

        .search-results {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            margin-top: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .search-results-content {
            padding: 20px;
        }
        .search-results {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            margin-top: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .search-results-content {
            padding: 20px;
        }
        /* Form Grid and Input Styling */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-bottom: 32px;
        }

        .form-grid.two-columns {
            grid-template-columns: repeat(2, 1fr);
        }

        .form-grid.full-width {
            grid-template-columns: 1fr;
        }

        .hospital-form-group {
            margin-bottom: 8px;
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
        }

        .label-icon {
            font-size: 16px;
        }

        .required-mark {
            color: #ef4444;
            font-weight: 700;
            margin-left: 4px;
        }

        .input-wrapper {
            margin-bottom: 8px;
            position: relative;
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .hospital-textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

        .hospital-select {
            appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }

        .select-arrow {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6b7280;
        }

        .input-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        .error-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .input-success {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
        }

        .success-message {
            color: #10b981;
            font-size: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .form-row {
            display: flex;
            gap: 16px;
            align-items: flex-end;
        }

        .form-row .hospital-form-group {
            flex: 1;
        }

        .form-row .hospital-form-group:last-child {
            flex: 0 0 auto;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
            cursor: pointer;
        }

        .checkbox-group label {
            font-size: 14px;
            color: #374151;
            cursor: pointer;
            user-select: none;
        }

        .radio-group {
            display: flex;
            gap: 16px;
            margin-bottom: 12px;
        }

        .radio-group .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .radio-group input[type="radio"] {
            width: 16px;
            height: 16px;
            accent-color: #667eea;
            cursor: pointer;
        }

        .radio-group label {
            font-size: 14px;
            color: #374151;
            cursor: pointer;
            user-select: none;
        }

    </style>
</head>
<body>
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
            <a href="#" class="nav-item">
                <span class="nav-icon">📋</span>
                Medical Records
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">🏥</span>
                Services
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
            <h2 style="margin-bottom: 28px; color: #222;">Welcome Gregorio</h2>
            <div class="dashboard-grid">
                <!-- Today's Appointments Section -->
                <div class="appointments-section" style="margin-top: 0; background: rgba(255,255,255,0.95); border-radius: 15px; padding: 25px; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
                    <h3 style="color: #6c336e; margin-bottom: 18px;">Today's Appointments</h3>
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
                            <?php
                            $appointments = [];
                            if (count($appointments) === 0): ?>
                                <tr>
                                    <td colspan="5" style="padding: 12px; text-align: center; color: #888;">
                                        No appointments today.
                                    </td>
                                </tr>
                            <?php else:
                                foreach ($appointments as $appt): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($appt['id']); ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($appt['name']); ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($appt['date']); ?></td>
                                        <td style="padding: 10px 8px;"><?php echo htmlspecialchars($appt['concern']); ?></td>
                                        <td style="padding: 10px 8px;">
                                            <select name="status[]" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #ccc;">
                                                <option value="Waiting">Waiting</option>
                                                <option value="Ongoing">Ongoing</option>
                                                <option value="Done">Done</option>
                                            </select>
                                        </td>
                                    </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
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
                        <tr style="background: #fff; border-bottom: 1px solid #f3e6f7;">
                            <td style="padding: 14px 18px;">PAT-2024-001</td>
                            <td style="padding: 14px 18px;">Maria Santos Garcia</td>
                            <td style="padding: 14px 18px;">28</td>
                            <td style="padding: 14px 18px;">Female</td>
                            <td style="padding: 14px 18px;">Active</td>
                            <td style="padding: 14px 18px;">
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <a href="#" 
                                       style="color: #2d0b3a; text-decoration: underline; font-weight: 500;" 
                                       onclick="event.preventDefault(); 
                                         document.getElementById('patient-list-section').style.display='none'; 
                                         document.getElementById('medical-records-section').style.display='block'; 
                                         document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                                         Array.from(document.querySelectorAll('.nav-item')).find(nav => nav.textContent.trim().includes('Medical Records'))?.classList.add('active');
                                       ">
                                        View Records
                                    </a>

                                </div>
                            </td>
                        </tr>
                        <tr style="background: #faf6fa;">
                            <td style="padding: 14px 18px;">PAT-2024-002</td>
                            <td style="padding: 14px 18px;">Ana Cruz Rodriguez</td>
                            <td style="padding: 14px 18px;">32</td>
                            <td style="padding: 14px 18px;">Female</td>
                            <td style="padding: 14px 18px;">Active</td>
                            <td style="padding: 14px 18px;">
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <a href="#" 
                                       style="color: #2d0b3a; text-decoration: underline; font-weight: 500;" 
                                       onclick="event.preventDefault(); document.getElementById('patient-list-section').style.display='none'; document.getElementById('medical-records-section').style.display='block';">
                                        View Records
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Services Dashboard Section (hidden by default) -->
        <div id="services-section" class="content-section" style="margin-top: 25px; display: none; background: rgba(255, 255, 255, 0.95);">
            <h2 style="color: #6c336e; font-size: 2rem; font-weight: bold; margin-bottom: 24px; letter-spacing: 1px;">PATIENT SERVICES</h2>
            
            <!-- Blank State - Default Display -->
            <div id="services-blank-state" style="text-align: center; padding: 80px 20px; color: #6b7280;">
                <div style="font-size: 64px; margin-bottom: 24px;">🏥</div>
                <h3 style="font-size: 24px; font-weight: 600; color: #374151; margin-bottom: 16px;">No Patient Selected</h3>
                <p style="font-size: 16px; margin-bottom: 32px; max-width: 400px; margin-left: auto; margin-right: auto;">
                    Search for a patient to view and manage their medical services, or add new service registrations.
                </p>
                
                <!-- Search Bar -->
                <div style="max-width: 400px; margin: 0 auto 24px auto;">
                    <div style="position: relative;">
                        <input type="text" id="services-search-blank" placeholder="Search by Patient ID or Name..." 
                               style="width: 100%; padding: 16px 20px; padding-right: 50px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-sizing: border-box;">
                        <button type="button" onclick="searchPatientsFromBlank()" 
                                style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 8px 12px; border-radius: 8px; cursor: pointer;">
                            🔍
                        </button>
                    </div>
                </div>
                
                <!-- Search Results for Blank State -->
                <div id="search-results-blank" style="display: none; max-width: 400px; margin: 0 auto;">
                    <div id="search-results-content-blank"></div>
                </div>
                
                <!-- Add Service Button -->
                <button class="btn-primary" id="openAddServiceModalFromBlank" style="padding: 16px 32px; font-size: 16px; font-weight: 600; border-radius: 12px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white; border: none; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);">
                    + Add New Service
                </button>
            </div>
            
            <!-- Services Content (hidden by default) -->
            <div id="services-content" style="display: none;">
                <!-- Patient Info Header -->
                <div style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 16px; padding: 24px; margin-bottom: 32px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 id="services-patient-name" style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">Patient Name</h3>
                            <p id="services-patient-id" style="font-size: 14px; color: #64748b; margin: 0;">Patient ID: ---</p>
                        </div>
                        <button onclick="goBackToPatientList()" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border: 2px solid #d1d5db; color: #6b7280; padding: 12px 24px; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                            ← Back to Patient List
                        </button>
                    </div>
                </div>
                
                <!-- Search and Add Service Row -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                    <div style="flex: 1; max-width: 400px;">
                        <div style="position: relative;">
                            <input type="text" id="services-search" placeholder="Search by Patient ID or Name..." 
                                   style="width: 100%; padding: 16px 20px; padding-right: 50px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-sizing: border-box;">
                            <button type="button" onclick="searchPatients()" 
                                    style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 8px 12px; border-radius: 8px; cursor: pointer;">
                                🔍
                            </button>
                        </div>
                    </div>
                    <button class="btn-primary" id="openAddServiceModal" style="padding: 16px 32px; font-size: 16px; font-weight: 600; border-radius: 12px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white; border: none; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);">
                        + Add New Service
                    </button>
                </div>
                
                <!-- Search Results -->
                <div id="search-results" style="display: none; margin-bottom: 24px;">
                    <div id="search-results-content"></div>
                </div>
                
                <!-- Services List -->
                <div style="background: #ffffff; border-radius: 16px; padding: 32px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);">
                    <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 24px 0;">Medical Services</h3>
                    <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                        <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
                        <p style="font-size: 16px; margin: 0;">No services registered yet. Add a new service to get started.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Records Dashboard Section (hidden by default) -->
        <div id="medical-records-section" class="content-section" style="margin-top: 30px; display: none; background: #f9fafc; border-radius: 20px;">
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
                                    <h2 style="font-size: 24px; font-weight: 600; color: #2d3748; margin-bottom: 4px;"> <!-- Patient Name from backend --> </h2>
                                    <div style="color: #718096; font-size: 14px; margin-bottom: 4px;"> <!-- Patient ID from backend --> </div>
                                    <div style="color: #4299e1; font-size: 14px;"> <!-- patient@email.com from backend --> </div>
                                </div>
                            </div>

                            <h3 style="color: #7c3a8a; font-size: 18px; font-weight: 700;">General Information</h3>
                            <!-- Awaiting backend data for patient info -->
                            <div class="info-item"><span class="info-label">Date of birth:</span> <span class="info-value"></span></div>
                            <div class="info-item"><span class="info-label">Age:</span> <span class="info-value"></span></div>
                            <div class="info-item"><span class="info-label">Gender:</span> <span class="info-value"></span></div>
                            <div class="info-item"><span class="info-label">Status:</span> <span class="info-value"></span></div>
                            <div class="info-item"><span class="info-label">Contact Number:</span> <span class="info-value"></span></div>
                            <div class="info-item"><span class="info-label">Occupation:</span> <span class="info-value"></span></div>
                            <div class="info-item"><span class="info-label">Address:</span> <span class="info-value"></span></div>
                            <h3 style="color: #7c3a8a; font-size: 18px; font-weight: 700; margin-top: 20px;">In Case of Emergency</h3>
                            <!-- Awaiting backend data for emergency contact -->
                            <div class="info-item"><span class="info-label">Name:</span> <span class="info-value"></span></div>
                            <div class="info-item"><span class="info-label">Contact Number:</span> <span class="info-value"></span></div>
                            <div class="info-item"><span class="info-label">Address:</span> <span class="info-value"></span></div>
                        </div>
                    </div>


                </div>
                <!-- Right Side: Analytics -->
                <div style="min-width: 340px; display: flex; flex-direction: column; gap: 24px;">
                    <div style="display: block; width: 100%;">
                        <div style="display: flex; flex-direction: column; gap: 24px; margin-bottom: 32px;">
                            <!-- Search Medical Records Card -->
                            <div style="background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(44,62,80,0.08); padding: 28px 32px; display: flex; flex-direction: column; gap: 20px;">
                                <h3 style="color: #232b3b; font-size: 20px; font-weight: 700; margin: 0;">Search Medical Records</h3>
                                
                                <div>
                                    <label style="color: #7c3aed; font-weight: 600; font-size: 16px; margin-bottom: 8px; display: block;">Visit Date</label>
                                    <input type="date" id="searchVisitDate" value="" placeholder="00/00/00" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff; transition: border 0.2s; box-sizing: border-box;">
                                </div>
                                
                                <div style="display: flex; gap: 12px;">
                                    <button type="button" class="btn-primary" id="searchVisitDateBtn" style="flex: 1; padding: 12px 16px; font-size: 15px; font-weight: 600; border-radius: 8px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white; border: none; cursor: pointer; transition: all 0.2s ease;">Search Visit Date</button>
                                    <button type="button" class="btn-secondary" id="addVisitDateBtn" style="flex: 1; padding: 12px 16px; font-size: 15px; font-weight: 600; border-radius: 8px; background: linear-gradient(90deg, #f8f9fe 0%, #e0e7ff 100%); color: #7c3aed; border: none; cursor: pointer; transition: all 0.2s ease;">Add Visit Date</button>
                                </div>
                                

                            </div>
                            
                            <!-- Age of Gestation Card -->
                            <div style="background: #fff; border-radius: 18px; box-shadow: 0 4px 24px rgba(44,62,80,0.08); padding: 28px 32px; display: flex; align-items: center; gap: 32px; justify-self: center;">
                                <!-- Left Column: Circle and Label -->
                                <div style="display: flex; flex-direction: column; align-items: center; min-width: 120px;">
                                    <div style="position: relative; width: 90px; height: 90px; margin-bottom: 10px;">
                                        <svg width="90" height="90" style="display:block;"><circle cx="45" cy="45" r="40" stroke="#e2e8f0" stroke-width="8" fill="none"/><circle cx="45" cy="45" r="40" stroke="#6ee7b7" stroke-width="8" fill="none" stroke-dasharray="251" stroke-dashoffset="80" stroke-linecap="round"/></svg>
                                        <div class="age-of-gestation-display" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 18px; font-weight: 400; color: #6ee7b7; text-align: center; line-height: 1;">0w</div>
                                    </div>
                                    <div style="font-size: 15px; color: #7c8ba1; font-weight: 500;">Age of Gestation</div>
                                </div>
                                
                                <!-- Right Column: Date Inputs -->
                                <div style="display: flex; flex-direction: column; gap: 20px; flex: 1;">
                                    <div>
                                        <label style="color: #7c3aed; font-weight: 600; font-size: 16px; margin-bottom: 8px; display: block;">Last Menstrual Period</label>
                                        <input type="date" id="lmpDate" placeholder="mm/dd/yyyy" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff; transition: border 0.2s; box-sizing: border-box;">
                                    </div>
                                    
                                    <div>
                                        <label style="color: #7c3aed; font-weight: 600; font-size: 16px; margin-bottom: 8px; display: block;">Expected Date of Confinement</label>
                                        <input type="date" id="edcDate" placeholder="mm/dd/yyyy" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 1.5px solid #e2e8f0; font-size: 15px; background: #fff; transition: border 0.2s; box-sizing: border-box;">
                                    </div>
                                    
                                    <!-- Save Button -->
                                    <div style="margin-top: 10px;">
                                        <button type="button" onclick="saveAndCalculateAOG(event)" style="width: 100%; padding: 10px 16px; border-radius: 8px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white; border: none; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s ease;">
                                            💾 SAVE
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
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
        <form id="physicalExamForm" onsubmit="return false;" style="padding: 40px; background: linear-gradient(180deg, #fafbfc 0%, #ffffff 100%); overflow-y: auto; flex: 1; scrollbar-width: thin; scrollbar-color: #cbd5e1 #f1f5f9;">
          <div id="physicalExamPage1">
            <!-- Visit Date Section - Compact Size -->
            <div style="margin-bottom: 24px; padding: 16px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 12px; border: 1px solid #e2e8f0;">
              <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 8px; display: block; letter-spacing: -0.3px;">📅 Visit Date</label>
              <div id="physicalExamVisitDateDisplay" style="padding: 12px 16px; border-radius: 8px; border: 2px solid #cbd5e1; font-size: 16px; background: #ffffff; color: #475569; font-weight: 600; text-align: center; box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.06);">--</div>
              <input type="hidden" name="visit_date" id="physicalExamHiddenVisitDate">
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
              <label style="color: #1e293b; font-weight: 600; font-size: 15px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <span style="color: #8b5cf6;">🔍</span> Physical Examination
              </label>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px 32px;">
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
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="cervix[]" value="soft" style="accent-color: #667eea;">Soft</label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="cervix[]" value="firm" style="accent-color: #667eea;">Firm</label>
                </div>
                <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                  <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">🫃 Uterus</span>
                  <span style="color: #1e293b; font-weight: 500; display: block; margin: 12px 0 8px 0; font-size: 13px;">Position:</span>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="uterus[]" value="mid" style="accent-color: #667eea;">Mid</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="uterus[]" value="anteflexed" style="accent-color: #667eea;">Anteflexed</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="uterus[]" value="retroflexed" style="accent-color: #667eea;">Retroflexed</label>
                  <span style="color: #1e293b; font-weight: 500; display: block; margin: 12px 0 8px 0; font-size: 13px;">Size:</span>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="uterus[]" value="small" style="accent-color: #667eea;">Small</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="uterus[]" value="large" style="accent-color: #667eea;">Large</label>
                  <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="uterus[]" value="normal" style="accent-color: #667eea;">Normal</label>
                  <span style="color: #1e293b; font-weight: 500; display: block; margin: 16px 0 8px 0; font-size: 13px;">Uterine Depth:</span>
                  <input type="text" name="uterine_depth" placeholder="e.g., 7 cm" style="width: 100%; padding: 12px 16px; border-radius: 8px; border: 2px solid #e2e8f0; font-size: 15px; background: #ffffff; transition: all 0.3s ease; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                </div>
              <div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;">
                <span style="color: #1e293b; font-weight: 600; font-size: 14px; display: block; margin-bottom: 12px;">🔬 ADNEXA</span>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="adnexa[]" value="mass" style="accent-color: #667eea;"> Mass</label>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 8px; color: #475569;"><input type="checkbox" name="adnexa[]" value="tenderness" style="accent-color: #667eea;"> Tenderness</label>
              </div>
        </div>
    </div>

              
        </div>

            <!-- Tab 2: Medical History -->
            <div id="medical-history-content" class="tab-content" style="display: none;">
            <!-- HEENT Section -->
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
             </div>


           </div>

            <!-- Tab 3: Obstetrical History -->
           <div id="obstetrical-history-content" class="tab-content" style="display: none;">
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


           </div>

            <!-- Tab 4: VAW Risk -->
           <div id="vaw-risk-content" class="tab-content" style="display: none;">
             <!-- VAW Risk Assessment -->
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


           </div>

                        <!-- Save and Close Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 16px; padding-top: 24px; border-top: 1px solid #e2e8f0; flex-shrink: 0; margin-bottom: 0;">
              <button type="button" onclick="saveAndUpdateSummary()" style="padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 500; background: linear-gradient(90deg, #10b981 0%, #059669 100%); color: white; border: none; cursor: pointer; transition: all 0.2s ease; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; min-width: 160px; box-shadow: none;" onmouseover="this.style.background='linear-gradient(90deg, #059669 0%, #047857 100%)'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='linear-gradient(90deg, #10b981 0%, #059669 100%)'; this.style.transform='translateY(0)'">💾 Save & Update Summary</button>
              <button type="button" id="closePhysicalExamModal" style="padding: 14px 28px; border-radius: 12px; font-size: 15px; font-weight: 600; background: #f8fafc; color: #475569; border: 2px solid #e2e8f0; cursor: pointer; transition: all 0.3s ease; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; min-width: 120px;">Done</button>
            </div>

         </form>
       </div>
     </div>

                <!-- Add Service Modal -->
                <div id="addServiceModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; justify-content: center; align-items: center;">
            <div class="hospital-modal" style="position: relative; border-radius: 20px; overflow: hidden; width: 90%; max-width: 900px; max-height: 90vh; overflow-y: auto;">
                <!-- Modal Header -->
                <div class="hospital-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <span style="font-size: 24px;">🏥</span>
                        </div>
                        <div class="header-text">
                            <h2>Medical Service Registration</h2>
                            <p>Register new medical services for the patient</p>
                        </div>
                    </div>
                    <button type="button" class="hospital-close" id="closeAddServiceModal">
                        <span style="font-size: 18px;">×</span>
                    </button>
                </div>

                <!-- Modal Form -->
                <form id="addServiceForm" class="hospital-form">
                    <div id="servicesContainer">
                        <!-- First Service Entry -->
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
                                            <option value="Dr. Maria Santos">Dr. Maria Santos - OB-GYN Specialist</option>
                                            <option value="Dr. Juan Rodriguez">Dr. Juan Rodriguez - Ultrasound Specialist</option>
                                            <option value="Dr. Ana Garcia">Dr. Ana Garcia - Laboratory Medicine</option>
                                            <option value="Dr. Carlos Lopez">Dr. Carlos Lopez - Pediatrics</option>
                                            <option value="Dr. Sofia Martinez">Dr. Sofia Martinez - Family Medicine</option>
                                            <option value="Dr. Miguel Torres">Dr. Miguel Torres - Emergency Medicine</option>
                                        </select>
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add More Service Button -->
                    <div style="text-align: center; margin: 24px 0;">
                        <button type="button" id="addMoreService" class="hospital-btn-cancel" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border: 2px solid #d1d5db; color: #6b7280; padding: 12px 24px; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                            + Add Another Service
                        </button>
                    </div>

                    <!-- Modal Actions -->
                    <div class="hospital-actions">
                        <button type="button" id="cancelAddServiceModal" class="hospital-btn-cancel">
                            Cancel
                        </button>
                        <button type="submit" class="hospital-btn-save">
                            <span>💾</span>
                            Save Services
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <script>
                // Core Services Functions
                document.addEventListener('DOMContentLoaded', function() {
            // Add Service Modal functionality
            const openAddServiceModal = document.getElementById('openAddServiceModal');
            const addServiceModal = document.getElementById('addServiceModal');
            const closeAddServiceModal = document.getElementById('closeAddServiceModal');
            const cancelAddServiceModal = document.getElementById('cancelAddServiceModal');
            const addMoreServiceBtn = document.getElementById('addMoreService');
            const servicesContainer = document.getElementById('servicesContainer');
            const addServiceForm = document.getElementById('addServiceForm');

            if (openAddServiceModal) {
                // Open modal
                openAddServiceModal.addEventListener('click', function() {
                    console.log('Add Service button clicked');
                    console.log('Modal element:', addServiceModal);
                    console.log('Modal current display:', addServiceModal.style.display);
                    addServiceModal.style.display = 'flex';
                    addServiceModal.style.backgroundColor = 'rgba(0, 0, 0, 0.8)'; // Make background darker for testing
                    console.log('Modal display set to:', addServiceModal.style.display);
                    console.log('Modal computed styles:', window.getComputedStyle(addServiceModal));
                    
                    // Force a repaint
                    addServiceModal.offsetHeight;
                });
            }

            if (closeAddServiceModal) {
                // Close modal (X button)
                closeAddServiceModal.addEventListener('click', function() {
                    addServiceModal.style.display = 'none';
                });
            }

            if (cancelAddServiceModal) {
                // Cancel modal (Cancel button)
                cancelAddServiceModal.addEventListener('click', function() {
                    addServiceModal.style.display = 'none';
                });
            }

            if (addServiceModal) {
                // Close modal when clicking outside
                addServiceModal.addEventListener('click', function(e) {
                    if (e.target === addServiceModal) {
                        addServiceModal.style.display = 'none';
                    }
                });
            }

            if (addMoreServiceBtn) {
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
                            </div>
                        </div>
                        
                        <div class="service-actions">
                            <button type="button" class="remove-service hospital-btn-cancel">
                                <span>🗑️ Remove Service</span>
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
            }

            if (addServiceForm) {
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
                        
                        // Close modal and reset form
                        addServiceModal.style.display = 'none';
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
            }

            // Services Page Functions
            window.populateServicesPage = function(patientName, patientId) {
                // Hide blank state and show content
                document.getElementById('services-blank-state').style.display = 'none';
                document.getElementById('services-content').style.display = 'block';
                
                // Update patient information
                document.getElementById('services-patient-name').textContent = patientName;
                document.getElementById('services-patient-id').textContent = `Patient ID: ${patientId}`;
            };

            window.goBackToPatientList = function() {
                document.getElementById('services-section').style.display = 'none';
                document.getElementById('patient-list-section').style.display = 'block';
                
                // Update navigation
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                Array.from(document.querySelectorAll('.nav-item')).find(nav => nav.textContent.trim().includes('Patients'))?.classList.add('active');
            };

            window.searchPatients = function() {
                const searchTerm = document.getElementById('services-search').value.toLowerCase().trim();
                const searchResults = document.getElementById('search-results');
                
                if (searchTerm === '') {
                    searchResults.style.display = 'none';
                    return;
                }
                
                // For now, just show a simple message
                searchResults.style.display = 'block';
                document.getElementById('search-results-content').innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #6b7280;">
                        <div style="font-size: 24px; margin-bottom: 8px;">🔍</div>
                        <p style="margin: 0; font-size: 14px;">Search functionality will be implemented with backend integration.</p>
                    </div>
                `;
            };

            window.searchPatientsFromBlank = function() {
                const searchTerm = document.getElementById('services-search-blank').value.toLowerCase().trim();
                const searchResults = document.getElementById('search-results-blank');
                
                if (searchTerm === '') {
                    searchResults.style.display = 'none';
                    return;
                }
                
                // For now, just show a simple message
                searchResults.style.display = 'block';
                document.getElementById('search-results-content-blank').innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #6b7280;">
                        <div style="font-size: 24px; margin-bottom: 8px;">🔍</div>
                        <p style="margin: 0; font-size: 14px;">Search functionality will be implemented with backend integration.</p>
                    </div>
                `;
            };
        });

        // Search and Navigation Functions
        window.initializeServicesSearch = function() {
            console.log('Initializing services search functionality...');
            
            // Initialize search inputs
            const servicesSearch = document.getElementById('services-search');
            const servicesSearchBlank = document.getElementById('services-search-blank');
            
            if (servicesSearch) {
                servicesSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    if (searchTerm.length >= 2) {
                        performServicesSearch(searchTerm, 'services');
                    } else {
                        hideSearchResults('services');
                    }
                });
            }
            
            if (servicesSearchBlank) {
                servicesSearchBlank.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    if (searchTerm.length >= 2) {
                        performServicesSearch(searchTerm, 'blank');
                    } else {
                        hideSearchResults('blank');
                    }
                });
            }
        };

        window.performServicesSearch = function(searchTerm, context) {
            console.log(`Performing services search for: "${searchTerm}" in context: ${context}`);
            
            // Simulate search results (replace with actual backend integration)
            const mockResults = [
                { id: 'P001', name: 'Maria Santos', age: 28, lastVisit: '2024-01-15' },
                { id: 'P002', name: 'Juan Rodriguez', age: 32, lastVisit: '2024-01-14' },
                { id: 'P003', name: 'Ana Garcia', age: 25, lastVisit: '2024-01-13' }
            ].filter(patient => 
                patient.name.toLowerCase().includes(searchTerm) || 
                patient.id.toLowerCase().includes(searchTerm)
            );
            
            displaySearchResults(mockResults, context);
        };

        window.displaySearchResults = function(results, context) {
            const resultsContainer = context === 'blank' ? 
                document.getElementById('search-results-blank') : 
                document.getElementById('search-results');
            
            const resultsContent = context === 'blank' ? 
                document.getElementById('search-results-content-blank') : 
                document.getElementById('search-results-content');
            
            if (!resultsContainer || !resultsContent) {
                console.error('Search results containers not found');
                return;
            }
            
            if (results.length === 0) {
                resultsContent.innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #6b7280;">
                        <div style="font-size: 24px; margin-bottom: 8px;">🔍</div>
                        <p style="margin: 0; font-size: 14px;">No patients found matching "${searchTerm}"</p>
                    </div>
                `;
            } else {
                const resultsHTML = results.map(patient => `
                    <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; cursor: pointer; transition: background 0.2s ease;" 
                         onmouseover="this.style.background='#f8fafc'" 
                         onmouseout="this.style.background='#ffffff'"
                         onclick="selectPatientForServices('${patient.id}', '${patient.name}')">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600; color: #1e293b; margin-bottom: 4px;">${patient.name}</div>
                                <div style="font-size: 14px; color: #64748b;">ID: ${patient.id} • Age: ${patient.age}</div>
                            </div>
                            <div style="font-size: 12px; color: #94a3b8;">Last Visit: ${patient.lastVisit}</div>
                        </div>
                    </div>
                `).join('');
                
                resultsContent.innerHTML = resultsHTML;
            }
            
            resultsContainer.style.display = 'block';
        };

        window.hideSearchResults = function(context) {
            const resultsContainer = context === 'blank' ? 
                document.getElementById('search-results-blank') : 
                document.getElementById('search-results');
            
            if (resultsContainer) {
                resultsContainer.style.display = 'none';
            }
        };

        window.selectPatientForServices = function(patientId, patientName) {
            console.log(`Patient selected for services: ${patientName} (${patientId})`);
            
            // Hide search results
            hideSearchResults('services');
            hideSearchResults('blank');
            
            // Clear search inputs
            const servicesSearch = document.getElementById('services-search');
            const servicesSearchBlank = document.getElementById('services-search-blank');
            
            if (servicesSearch) servicesSearch.value = '';
            if (servicesSearchBlank) servicesSearchBlank.value = '';
            
            // Populate services page with patient info
            populateServicesPage(patientName, patientId);
            
            // Show success message
            showNotification(`Patient ${patientName} selected for services`, 'success');
        };

        window.showNotification = function(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 16px 24px;
                border-radius: 12px;
                color: white;
                font-weight: 600;
                z-index: 10000;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
                transform: translateX(100%);
                transition: transform 0.3s ease;
                max-width: 300px;
            `;
            
            // Set background color based on type
            switch (type) {
                case 'success':
                    notification.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                    break;
                case 'error':
                    notification.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                    break;
                case 'warning':
                    notification.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                    break;
                default:
                    notification.style.background = 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        };

        window.initializeServicesNavigation = function() {
            console.log('Initializing services navigation...');
            
            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Escape key to close modals
                if (e.key === 'Escape') {
                    const addServiceModal = document.getElementById('addServiceModal');
                    if (addServiceModal && addServiceModal.style.display === 'flex') {
                        addServiceModal.style.display = 'none';
                        showNotification('Modal closed', 'info');
                    }
                }
                
                // Ctrl/Cmd + K to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    const servicesSearch = document.getElementById('services-search');
                    const servicesSearchBlank = document.getElementById('services-search-blank');
                    
                    if (servicesSearch && servicesSearch.offsetParent !== null) {
                        servicesSearch.focus();
                    } else if (servicesSearchBlank && servicesSearchBlank.offsetParent !== null) {
                        servicesSearchBlank.focus();
                    }
                }
            });
            
            // Initialize search on page load
            initializeServicesSearch();
        };

        // Initialize services functionality when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeServicesNavigation);
        } else {
            initializeServicesNavigation();
        }

        // Auto Populate Doctor Function
        window.initializeDoctorAutoPopulate = function() {
            console.log('Initializing doctor auto-populate functionality...');
            
            // Doctor database (replace with actual backend data)
            const doctorDatabase = [
                {
                    id: 'DOC001',
                    name: 'Dr. Maria Santos',
                    specialization: 'OB-GYN Specialist',
                    department: 'Obstetrics & Gynecology',
                    availability: 'Mon-Fri, 8AM-5PM',
                    contact: '+63 912 345 6789',
                    email: 'maria.santos@clinic.com',
                    experience: '15 years',
                    languages: ['English', 'Tagalog', 'Spanish']
                },
                {
                    id: 'DOC002',
                    name: 'Dr. Juan Rodriguez',
                    specialization: 'Ultrasound Specialist',
                    department: 'Radiology',
                    availability: 'Mon-Sat, 9AM-6PM',
                    contact: '+63 923 456 7890',
                    email: 'juan.rodriguez@clinic.com',
                    experience: '12 years',
                    languages: ['English', 'Tagalog']
                },
                {
                    id: 'DOC003',
                    name: 'Dr. Ana Garcia',
                    specialization: 'Laboratory Medicine',
                    department: 'Pathology',
                    availability: 'Mon-Fri, 7AM-4PM',
                    contact: '+63 934 567 8901',
                    email: 'ana.garcia@clinic.com',
                    experience: '18 years',
                    languages: ['English', 'Tagalog', 'Mandarin']
                },
                {
                    id: 'DOC004',
                    name: 'Dr. Carlos Lopez',
                    specialization: 'Pediatrics',
                    department: 'Pediatrics',
                    availability: 'Mon-Fri, 8AM-6PM',
                    contact: '+63 945 678 9012',
                    email: 'carlos.lopez@clinic.com',
                    experience: '20 years',
                    languages: ['English', 'Tagalog', 'English']
                },
                {
                    id: 'DOC005',
                    name: 'Dr. Sofia Martinez',
                    specialization: 'Family Medicine',
                    department: 'Primary Care',
                    availability: 'Mon-Sat, 8AM-7PM',
                    contact: '+63 956 789 0123',
                    email: 'sofia.martinez@clinic.com',
                    experience: '14 years',
                    languages: ['English', 'Tagalog', 'Bisaya']
                },
                {
                    id: 'DOC006',
                    name: 'Dr. Miguel Torres',
                    specialization: 'Emergency Medicine',
                    department: 'Emergency',
                    availability: '24/7 (Rotating)',
                    contact: '+63 967 890 1234',
                    email: 'miguel.torres@clinic.com',
                    experience: '16 years',
                    languages: ['English', 'Tagalog']
                }
            ];

            // Store doctor database globally for access
            window.doctorDatabase = doctorDatabase;

            // Initialize doctor selection dropdowns
            initializeDoctorDropdowns();
            
            // Add doctor search functionality
            initializeDoctorSearch();
        };

        window.initializeDoctorDropdowns = function() {
            const doctorSelects = document.querySelectorAll('select[name="service_doctor[]"]');
            
            doctorSelects.forEach(select => {
                // Clear existing options except the first one
                const firstOption = select.querySelector('option:first-child');
                select.innerHTML = '';
                if (firstOption) {
                    select.appendChild(firstOption);
                }
                
                // Add doctor options
                window.doctorDatabase.forEach(doctor => {
                    const option = document.createElement('option');
                    option.value = doctor.name;
                    option.textContent = `${doctor.name} - ${doctor.specialization}`;
                    option.dataset.doctorId = doctor.id;
                    option.dataset.specialization = doctor.specialization;
                    option.dataset.department = doctor.department;
                    select.appendChild(option);
                });
            });
        };

        window.initializeDoctorSearch = function() {
            // Add doctor search input to the modal
            const addServiceModal = document.getElementById('addServiceModal');
            if (addServiceModal) {
                const modalHeader = addServiceModal.querySelector('.hospital-header');
                if (modalHeader) {
                    // Check if search input already exists
                    if (!document.getElementById('doctor-search-container')) {
                        const searchContainer = document.createElement('div');
                        searchContainer.id = 'doctor-search-container';
                        searchContainer.style.cssText = `
                            position: absolute;
                            top: 24px;
                            left: 24px;
                            z-index: 10;
                        `;
                        
                        const searchInput = document.createElement('input');
                        searchInput.type = 'text';
                        searchInput.placeholder = '🔍 Search doctors...';
                        searchInput.id = 'doctor-search-input';
                        searchInput.style.cssText = `
                            padding: 8px 12px;
                            border: 1px solid rgba(255, 255, 255, 0.3);
                            border-radius: 8px;
                            background: rgba(255, 255, 255, 0.1);
                            color: white;
                            font-size: 14px;
                            width: 200px;
                        `;
                        
                        searchInput.addEventListener('input', function() {
                            const searchTerm = this.value.toLowerCase().trim();
                            filterDoctors(searchTerm);
                        });
                        
                        searchContainer.appendChild(searchInput);
                        modalHeader.appendChild(searchContainer);
                    }
                }
            }
        };

        window.filterDoctors = function(searchTerm) {
            const doctorSelects = document.querySelectorAll('select[name="service_doctor[]"]');
            
            doctorSelects.forEach(select => {
                const options = select.querySelectorAll('option');
                
                options.forEach(option => {
                    if (option.value === '') return; // Skip placeholder option
                    
                    const doctorName = option.textContent.toLowerCase();
                    const doctorId = option.dataset.doctorId;
                    const doctor = window.doctorDatabase.find(d => d.id === doctorId);
                    
                    if (doctor) {
                        const matchesSearch = 
                            doctor.name.toLowerCase().includes(searchTerm) ||
                            doctor.specialization.toLowerCase().includes(searchTerm) ||
                            doctor.department.toLowerCase().includes(searchTerm);
                        
                        option.style.display = matchesSearch ? '' : 'none';
                    }
                });
            });
        };

        window.autoPopulateDoctorInfo = function(doctorSelect) {
            const selectedDoctorName = doctorSelect.value;
            if (!selectedDoctorName) return;
            
            const doctor = window.doctorDatabase.find(d => d.name === selectedDoctorName);
            if (!doctor) return;
            
            // Find the service entry container
            const serviceEntry = doctorSelect.closest('.service-entry');
            if (!serviceEntry) return;
            
            // Create or update doctor info display
            let doctorInfoDisplay = serviceEntry.querySelector('.doctor-info-display');
            if (!doctorInfoDisplay) {
                doctorInfoDisplay = document.createElement('div');
                doctorInfoDisplay.className = 'doctor-info-display';
                doctorInfoDisplay.style.cssText = `
                    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                    border: 1px solid #d1d5db;
                    border-radius: 12px;
                    padding: 16px;
                    margin-top: 16px;
                    font-size: 14px;
                `;
                
                // Insert after the form grid
                const formGrid = serviceEntry.querySelector('.form-grid');
                if (formGrid) {
                    formGrid.parentNode.insertBefore(doctorInfoDisplay, formGrid.nextSibling);
                }
            }
            
            // Populate doctor information
            doctorInfoDisplay.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px;">
                        👨‍⚕️
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #1e293b; margin-bottom: 4px;">${doctor.name}</div>
                        <div style="color: #64748b; font-size: 12px;">${doctor.specialization}</div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 12px;">
                    <div>
                        <span style="color: #6b7280;">Department:</span>
                        <span style="color: #374151; font-weight: 500;"> ${doctor.department}</span>
                    </div>
                    <div>
                        <span style="color: #6b7280;">Experience:</span>
                        <span style="color: #374151; font-weight: 500;"> ${doctor.experience}</span>
                    </div>
                    <div>
                        <span style="color: #6b7280;">Availability:</span>
                        <span style="color: #374151; font-weight: 500;"> ${doctor.availability}</span>
                    </div>
                    <div>
                        <span style="color: #6b7280;">Contact:</span>
                        <span style="color: #374151; font-weight: 500;"> ${doctor.contact}</span>
                    </div>
                </div>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                    <div style="color: #6b7280; margin-bottom: 4px;">Languages:</div>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        ${doctor.languages.map(lang => 
                            `<span style="background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 6px; font-size: 11px;">${lang}</span>`
                        ).join('')}
                    </div>
                </div>
            `;
        };

        window.initializeDoctorEventListeners = function() {
            // Add change event listeners to doctor selects
            document.addEventListener('change', function(e) {
                if (e.target.name === 'service_doctor[]') {
                    autoPopulateDoctorInfo(e.target);
                }
            });
            
            // Add event listeners for dynamically added doctor selects
            document.addEventListener('DOMContentLoaded', function() {
                const addMoreServiceBtn = document.getElementById('addMoreService');
                if (addMoreServiceBtn) {
                    addMoreServiceBtn.addEventListener('click', function() {
                        // Wait for the new service entry to be added
                        setTimeout(() => {
                            const newDoctorSelects = document.querySelectorAll('select[name="service_doctor[]"]');
                            newDoctorSelects.forEach(select => {
                                if (!select.dataset.initialized) {
                                    select.dataset.initialized = 'true';
                                    // Re-initialize the dropdown for new entries
                                    initializeDoctorDropdowns();
                                }
                            });
                        }, 100);
                    });
                }
            });
        };

        // Initialize doctor functionality
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initializeDoctorAutoPopulate();
                initializeDoctorEventListeners();
            });
        } else {
            initializeDoctorAutoPopulate();
            initializeDoctorEventListeners();
        }

        // Debug: Test function to manually show Services section
        window.testShowServices = function() {
            console.log('Testing Services section display...');
            const servicesSection = document.getElementById('services-section');
            if (servicesSection) {
                servicesSection.style.display = 'block';
                console.log('Services section manually displayed');
                console.log('Services section element:', servicesSection);
                console.log('Services section display style:', servicesSection.style.display);
            } else {
                console.error('Services section not found in test function');
            }
        };

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
                const servicesSection = document.getElementById('services-section');
                
                // Debug: Log the elements found
                console.log('Main content:', mainContent);
                console.log('Patient list:', patientList);
                console.log('Medical records:', medRecords);
                console.log('Services section:', servicesSection);
                
                if (this.textContent.trim().includes('Patients')) {
                    console.log('Showing Patients section');
                    mainContent.style.display = 'none';
                    patientList.style.display = '';
                    medRecords.style.display = 'none';
                    
                    // Hide services section
                    const servicesSection = document.getElementById('services-section');
                    if (servicesSection) servicesSection.style.display = 'none';
                } else if (this.textContent.trim().includes('Medical Records')) {
                    console.log('Showing Medical Records section');
                    mainContent.style.display = 'none';
                    patientList.style.display = 'none';
                    medRecords.style.display = '';
                    
                    // Hide services section
                    const servicesSection = document.getElementById('services-section');
                    if (servicesSection) servicesSection.style.display = 'none';
                } else if (this.textContent.trim().includes('Home')) {
                    console.log('Showing Home section');
                    mainContent.style.display = '';
                    patientList.style.display = 'none';
                    medRecords.style.display = 'none';
                    
                    // Hide services section
                    const servicesSection = document.getElementById('services-section');
                    if (servicesSection) servicesSection.style.display = 'none';
                } else if (this.textContent.trim().includes('Services')) {
                    console.log('Showing Services section');
                    console.log('Services section element:', servicesSection);
                    
                    mainContent.style.display = 'none';
                    patientList.style.display = 'none';
                    medRecords.style.display = 'none';
                    
                    // Show services section
                    if (servicesSection) {
                        servicesSection.style.display = 'block';
                        console.log('Services section displayed successfully');
                        console.log('Services section display style:', servicesSection.style.display);
                    } else {
                        console.error('Services section not found');
                    }
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
        document.getElementById('visitAnalyticsForm').onsubmit = function(e) {
          e.preventDefault();
          
          // Collect visit analytics data
          const visitData = collectVisitAnalyticsData();
          console.log('Collected visit analytics data:', visitData);
          
          // Add the data to the Visit Analytics table
          addVisitAnalyticsToTable(visitData);
          
          // Show success message
          alert('Visit analytics saved successfully!');
          
          // Close the modal
          document.getElementById('visitAnalyticsModal').style.display = 'none';
        };

        // Physical Examination Add New Modal logic
        document.addEventListener('DOMContentLoaded', function() {
          const physicalExamAddNewBtn = document.getElementById('physicalExamAddNewBtn');
          
          if (physicalExamAddNewBtn) {
            physicalExamAddNewBtn.onclick = function() {
              // Get the selected visit date from the Search Medical Records card
              const searchVisitDateInput = document.getElementById('searchVisitDate');
              const physicalExamVisitDateDisplay = document.getElementById('physicalExamVisitDateDisplay');
              const physicalExamHiddenVisitDate = document.getElementById('physicalExamHiddenVisitDate');
              
              if (searchVisitDateInput && searchVisitDateInput.value) {
                // Format the date for display (MM/DD/YYYY)
                const date = new Date(searchVisitDateInput.value);
                const formattedDate = `${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}/${date.getFullYear()}`;
                
                physicalExamVisitDateDisplay.textContent = formattedDate;
                physicalExamHiddenVisitDate.value = searchVisitDateInput.value;
              } else {
                physicalExamVisitDateDisplay.textContent = '--';
                physicalExamHiddenVisitDate.value = '';
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
        function updateVAWRiskSummary(elementId, riskData, condition) {
            const element = document.getElementById(elementId);
            console.log('Updating VAW Risk summary:', elementId, 'with data:', riskData, 'for condition:', condition);
            
            if (element) {
                if (riskData && riskData.length > 0) {
                    // Check if the specific condition is present
                    const hasCondition = riskData.includes(condition);
                    console.log('Condition check:', condition, 'hasCondition:', hasCondition);
                    
                    if (hasCondition) {
                        element.textContent = '⚠️ Risk Identified';
                        element.style.color = '#dc2626'; // Red color for risk findings
                        element.style.fontWeight = '600';
                        console.log('Updated element to show risk');
                    } else {
                        element.textContent = '—';
                        element.style.color = '#64748b';
                        element.style.fontWeight = '500';
                        console.log('Updated element to show normal');
                    }
                } else {
                    element.textContent = '—';
                    element.style.color = '#64748b';
                    element.style.fontWeight = '500';
                    console.log('Updated element to show no data');
                }
            } else {
                console.log('Element not found:', elementId);
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
            
            // Sample data - in a real application, this would come from a database
            const sampleAssessmentData = {
                '2025-08-23': {
                    physicalExamination: {
                        conjunctiva: 'Normal',
                        neck: 'No abnormalities',
                        thorax: 'Clear breath sounds',
                        abdomen: 'Soft, non-tender',
                        extremities: 'No edema',
                        breast: 'No masses detected'
                    },
                    pelvicExamination: {
                        perinium: 'Normal',
                        vagina: 'No discharge',
                        cervix: 'Soft, mid-position',
                        uterus: 'Anteflexed, normal size',
                        adnexa: 'No masses'
                    },
                    medicalHistory: {
                        heent: 'No significant findings',
                        chest_heart: 'Normal heart sounds',
                        abdomen: 'No masses',
                        genital: 'No abnormalities',
                        extremities: 'No varicosities',
                        skin: 'Normal color'
                    },
                    obstetricalHistory: {
                        full_term: '2',
                        abortions: '0',
                        premature: '0',
                        living_children: '2',
                        last_delivery: '2023-05-15',
                        lmp: '2025-01-15'
                    },
                    vawRisk: {
                        domestic_violence: 'None reported',
                        sexual_violence: 'None reported',
                        psychological_violence: 'None reported',
                        economic_violence: 'None reported',
                        referred_to: 'Not applicable'
                    }
                }
            };
            
            const data = sampleAssessmentData[selectedDate];
            if (data) {
                // Update Physical Examination Summary
                updateSummaryCardField('conjunctiva', data.physicalExamination.conjunctiva);
                updateSummaryCardField('neck', data.physicalExamination.neck);
                updateSummaryCardField('thorax', data.physicalExamination.thorax);
                updateSummaryCardField('abdomen', data.physicalExamination.abdomen);
                updateSummaryCardField('extremities', data.physicalExamination.extremities);
                updateSummaryCardField('breast', data.physicalExamination.breast);
                
                // Update Pelvic Examination Summary
                updateSummaryCardField('perinium', data.pelvicExamination.perinium);
                updateSummaryCardField('vagina', data.pelvicExamination.vagina);
                updateSummaryCardField('adnexa', data.pelvicExamination.adnexa);
                updateSummaryCardField('cervix', data.pelvicExamination.cervix);
                updateSummaryCardField('uterus', data.pelvicExamination.uterus);
                
                // Update Medical History Summary
                updateSummaryCardField('heent', data.medicalHistory.heent);
                updateSummaryCardField('chest_heart', data.medicalHistory.chest_heart);
                updateSummaryCardField('abdomen', data.medicalHistory.abdomen);
                updateSummaryCardField('genital', data.medicalHistory.genital);
                updateSummaryCardField('extremities', data.medicalHistory.extremities);
                updateSummaryCardField('skin', data.medicalHistory.skin);
                updateSummaryCardField('sti_risks', data.medicalHistory.sti_risks);
                
                // Update Obstetrical History Summary
                updateSummaryCardField('full_term', data.obstetricalHistory.full_term);
                updateSummaryCardField('abortions', data.obstetricalHistory.abortions);
                updateSummaryCardField('premature', data.obstetricalHistory.premature);
                updateSummaryCardField('living_children', data.obstetricalHistory.living_children);
                updateSummaryCardField('last_delivery', data.obstetricalHistory.last_delivery);
                updateSummaryCardField('lmp', data.obstetricalHistory.lmp);
                
                // Update VAW Risk Summary
                updateSummaryCardField('domestic_violence', data.vawRisk.domestic_violence);
                updateSummaryCardField('sexual_violence', data.vawRisk.sexual_violence);
                updateSummaryCardField('psychological_violence', data.vawRisk.psychological_violence);
                updateSummaryCardField('economic_violence', data.vawRisk.economic_violence);
                updateSummaryCardField('referred_to', data.vawRisk.referred_to);
                
                console.log('Patient assessment populated successfully');
            } else {
                console.log('No patient assessment data found for the selected date');
            }
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
        document.addEventListener('DOMContentLoaded', function() {
            // Handle main navigation
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const text = this.textContent.trim();
                    
                    // Remove active class from all nav items
                    navItems.forEach(nav => nav.classList.remove('active'));
                    // Add active class to clicked item
                    this.classList.add('active');
                    
                    // Hide all sections
                    document.querySelector('.main-content > .content-section').style.display = 'none';
                    document.getElementById('patient-list-section').style.display = 'none';
                    document.getElementById('medical-records-section').style.display = 'none';
                    
                    // Show appropriate section
                    if (text.includes('Home')) {
                        document.querySelector('.main-content > .content-section').style.display = 'block';
                    } else if (text.includes('Patients')) {
                        document.getElementById('patient-list-section').style.display = 'block';
                    } else if (text.includes('Medical Records')) {
                        document.getElementById('medical-records-section').style.display = 'block';
                        setTimeout(initializeAgeOfGestation, 200);
                    }
                });
            });
        });
    </script>
</body>
</html>

