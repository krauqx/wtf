<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = 'localhost';
$dbname = 'jam_db';
$username = 'root';
$password = '';

// SMS API credentials
$sms_token= ""; //SMS API TOKEN HERE(WILL BE PROVIDED LATER)
$sms_send_endpoint = 'https://rest.moceanapi.com/rest/2/sms';

// PDO Connection
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $exists = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
    if (!$exists) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                contact VARCHAR(20) NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('patient','staff','admin','midwife','clerk') NOT NULL,
                date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }
    $existsOtp = $pdo->query("SHOW TABLES LIKE 'otp_requests'")->fetch();
    if (!$existsOtp) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS otp_requests (
                id INT PRIMARY KEY AUTO_INCREMENT,
                contact VARCHAR(50) NOT NULL,
                otp_code VARCHAR(6) NOT NULL,
                channel ENUM('sms','email') DEFAULT 'sms',
                purpose ENUM('register','verify') DEFAULT 'register',
                status ENUM('pending','used','expired') DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NOT NULL,
                used_at DATETIME DEFAULT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                delivery_status ENUM('sent','failed') DEFAULT NULL,
                INDEX idx_contact_status (contact, status),
                INDEX idx_expiry (expires_at),
                INDEX idx_created (created_at)
            )
        ");
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize User Role
$_SESSION['user_role'] = $_SESSION['user_role'] ?? null;

// Map user_id to patient_id if role is patient
if ($_SESSION['user_role'] === 'patient' && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT patient_id FROM patient_records WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $_SESSION['patient_id'] = $result['patient_id'] ?? null;
}
?>
