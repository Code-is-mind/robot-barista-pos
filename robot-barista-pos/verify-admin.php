<?php
/**
 * Verify Admin User Script
 * 
 * This script checks if the admin user exists and can verify/reset the password
 * Run this to troubleshoot login issues
 */

require_once __DIR__ . '/config/database.php';

echo "=== Admin User Verification ===\n\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to database\n\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        echo "✗ Users table does not exist!\n";
        echo "Run setup.php first.\n";
        exit(1);
    }
    
    echo "✓ Users table exists\n\n";
    
    // Check for admin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "✗ Admin user does not exist!\n";
        echo "Creating admin user now...\n\n";
        
        $username = 'admin';
        $password = 'admin123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, 'System Administrator', 'admin', 1]);
        
        echo "✓ Admin user created!\n";
        echo "  Username: admin\n";
        echo "  Password: admin123\n";
    } else {
        echo "✓ Admin user exists\n";
        echo "  ID: " . $admin['id'] . "\n";
        echo "  Username: " . $admin['username'] . "\n";
        echo "  Full Name: " . ($admin['full_name'] ?? 'N/A') . "\n";
        echo "  Role: " . ($admin['role'] ?? 'N/A') . "\n";
        echo "  Active: " . ($admin['is_active'] ? 'YES' : 'NO') . "\n";
        echo "  Created: " . ($admin['created_at'] ?? 'N/A') . "\n\n";
        
        // Test password
        echo "Testing password 'admin123'...\n";
        if (password_verify('admin123', $admin['password'])) {
            echo "✓ Password 'admin123' is correct!\n\n";
        } else {
            echo "✗ Password 'admin123' does NOT match!\n";
            echo "Resetting password to 'admin123'...\n\n";
            
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
            $stmt->execute([$hashedPassword]);
            
            echo "✓ Password reset to 'admin123'\n\n";
        }
    }
    
    echo "=== Verification Complete ===\n";
    echo "You can now login with:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
