<?php
session_start();

// Database configuration
include_once '../config/config.php';
include_once '../url.php';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } else {
        // Check user credentials and role
        $stmt = $pdo->prepare("SELECT id, email, password, first_name, role 
                               FROM users 
                               WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'staff') {
                // Login successful for staff
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect to dashboard
                header('Location: ' . URL_STAFF_DASH );
                exit();
            } else {
                // User exists but is not staff
                 $error_message = 'User not authorized as medical staff.';
            }
        } else {
            $error_message = 'Invalid email or password.';
        }
    }
}

// Check if user is already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'staff') {
    header('Location: ' . URL_STAFF_DASH );
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Staff Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            display: flex;
        }

        .form-container {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .welcome-container {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
            font-weight: 300;
        }

        .subtitle {
            color: #666;
            margin-bottom: 40px;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            background-color: #f8f9fa;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            background-color: white;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            margin-bottom: 20px;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .toggle-form {
            text-align: center;
            color: #666;
        }

        .toggle-form a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .toggle-form a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .welcome-content h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
            font-weight: 300;
        }

        .welcome-content p {
            font-size: 1.1em;
            line-height: 1.6;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                max-width: 400px;
            }
            
            .welcome-container {
                order: -1;
                padding: 30px 20px;
            }
            
            .form-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Medical Staff Login</h1>
            <p class="subtitle">Please login to your medical staff account</p>

            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" name="login" class="btn">Login</button>
            </form>

            <div style="margin-top: 20px; text-align: center;">
                <a href="../front.php" class="btn" style="background: #f0f0f0; color: #333; text-decoration: none; display: inline-block; padding: 15px; border-radius: 8px; width: 100%; box-sizing: border-box;">Back to Home</a>
            </div>
        </div>

        <div class="welcome-container">
            <div class="welcome-content">
                <h2>Welcome Medical Staff!</h2>
                <p>Login to access your medical staff dashboard and manage patient records.</p>
            </div>
        </div>
    </div>
</body>
</html>

<?php
/*
SQL TABLE STRUCTURE - Run this in your database to create the users table:

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

*/
?>

<?php
/*
ADDITIONAL FILES NEEDED:

1. dashboard.php - Create this file for the logged-in user dashboard:

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <p>You have successfully logged in to your dashboard.</p>
    </div>
</body>
</html>

2. logout.php - Create this file to handle user logout:

<?php
session_start();
session_destroy();
header('Location: login.php');
exit();
?>

*/
?>