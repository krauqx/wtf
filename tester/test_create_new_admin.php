<?php
// test_create_new_admin.php

session_start();
include_once __DIR__ . '/../config/config.php';

try {
    // Connect to DB
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);

    // Pseudo admin account details
    $email = "admin@jam";
    $passwordPlain = "admin123";
    $firstName = "admin1";
    $lastName = "admin1";
    $role = "admin";

    // Hash the password for security
    $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);

    // Insert query
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) 
                           VALUES (:first_name, :last_name, :email, :password, :role)");

    $stmt->execute([
        ':first_name' => $firstName,
        ':last_name'  => $lastName,
        ':email'      => $email,
        ':password'   => $passwordHash,
        ':role'       => $role
    ]);

    echo "Pseudo admin account created successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>