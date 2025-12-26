<<<<<<< HEAD
<?php
/**
 * Database Setup Script
 * 
 * This script creates the database, tables, and seeds initial data including admin user
 * Run this once after cloning the project
 * 
 * Usage: php setup.php
 * 
 * SECURITY: This script will delete itself after successful execution
 */

// Load database configuration
require_once __DIR__ . '/config/database.php';

echo "=== Robot Barista POS Setup ===\n\n";

try {
    // Connect to MySQL without database first
    echo "Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to MySQL server\n";
    echo "  Host: " . DB_HOST . "\n";
    echo "  User: " . DB_USER . "\n\n";
    
    // Create database if not exists
    echo "Creating database...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database created/verified\n\n";
    
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if setup already ran by checking for admin user
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
        $stmt->execute();
        if ($stmt->fetch()) {
            echo "⚠ Setup already completed! Admin user exists.\n";
            echo "If you need to reset, manually drop the database first.\n\n";
            
            // Delete this file for security
            if (unlink(__FILE__)) {
                echo "✓ Setup file deleted for security\n";
            }
            exit(0);
        }
    }
    
    // Read and execute schema
    echo "Loading database schema...\n";
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Remove CREATE DATABASE and USE statements as we're already connected
    $schema = preg_replace('/CREATE DATABASE.*?;/s', '', $schema);
    $schema = preg_replace('/USE .*?;/s', '', $schema);
    
    // Split into individual statements and execute with error handling
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Skip if table/entry already exists or duplicate key
                $errorCode = $e->getCode();
                $errorMsg = $e->getMessage();
                
                // Error codes: 1050 = table exists, 1062 = duplicate entry
                if ($errorCode == '23000' || $errorCode == '42S01' || 
                    strpos($errorMsg, 'already exists') !== false || 
                    strpos($errorMsg, 'Duplicate entry') !== false) {
                    // Skip silently
                    continue;
                }
                
                // For other errors, throw
                throw $e;
            }
        }
    }
    echo "✓ Database schema created\n\n";
    
    // Create admin user
    echo "Creating admin user...\n";
    $username = 'admin';
    $password = 'admin123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        echo "⚠ Admin user already exists, updating password...\n";
        $stmt = $pdo->prepare("UPDATE users SET password = ?, is_active = 1 WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, 'System Administrator', 'admin', 1]);
    }
    
    echo "✓ Admin user created/updated\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n\n";
    
    // Verify setup
    echo "Verifying setup...\n";
    $tables = ['categories', 'products', 'modifiers', 'orders', 'users', 'settings'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "  ✓ $table: $count records\n";
    }
    
    echo "\n=== Setup Complete! ===\n";
    echo "You can now login to the admin panel:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n";
    echo "\nIMPORTANT: Change the default password after first login!\n\n";
    
    // Delete this file for security
    echo "Deleting setup file for security...\n";
    if (unlink(__FILE__)) {
        echo "✓ Setup file deleted successfully\n";
        echo "\nThis setup script has been removed and cannot be run again.\n";
    } else {
        echo "⚠ Warning: Could not delete setup.php - please remove it manually!\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nSetup failed. The setup.php file was NOT deleted.\n";
    echo "Fix the error and run setup again.\n";
    exit(1);
}
=======
<?php
/**
 * Database Setup Script
 * 
 * This script creates the database, tables, and seeds initial data including admin user
 * Run this once after cloning the project
 * 
 * Usage: php setup.php
 * 
 * SECURITY: This script will delete itself after successful execution
 */

// Load database configuration
require_once __DIR__ . '/config/database.php';

echo "=== Robot Barista POS Setup ===\n\n";

try {
    // Connect to MySQL without database first
    echo "Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to MySQL server\n";
    echo "  Host: " . DB_HOST . "\n";
    echo "  User: " . DB_USER . "\n\n";
    
    // Create database if not exists
    echo "Creating database...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database created/verified\n\n";
    
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if setup already ran by checking for admin user
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
        $stmt->execute();
        if ($stmt->fetch()) {
            echo "⚠ Setup already completed! Admin user exists.\n";
            echo "If you need to reset, manually drop the database first.\n\n";
            
            // Delete this file for security
            if (unlink(__FILE__)) {
                echo "✓ Setup file deleted for security\n";
            }
            exit(0);
        }
    }
    
    // Read and execute schema
    echo "Loading database schema...\n";
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Remove CREATE DATABASE and USE statements as we're already connected
    $schema = preg_replace('/CREATE DATABASE.*?;/s', '', $schema);
    $schema = preg_replace('/USE .*?;/s', '', $schema);
    
    // Split into individual statements and execute with error handling
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Skip if table/entry already exists or duplicate key
                $errorCode = $e->getCode();
                $errorMsg = $e->getMessage();
                
                // Error codes: 1050 = table exists, 1062 = duplicate entry
                if ($errorCode == '23000' || $errorCode == '42S01' || 
                    strpos($errorMsg, 'already exists') !== false || 
                    strpos($errorMsg, 'Duplicate entry') !== false) {
                    // Skip silently
                    continue;
                }
                
                // For other errors, throw
                throw $e;
            }
        }
    }
    echo "✓ Database schema created\n\n";
    
    // Create admin user
    echo "Creating admin user...\n";
    $username = 'admin';
    $password = 'admin123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        echo "⚠ Admin user already exists, updating password...\n";
        $stmt = $pdo->prepare("UPDATE users SET password = ?, is_active = 1 WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, 'System Administrator', 'admin', 1]);
    }
    
    echo "✓ Admin user created/updated\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n\n";
    
    // Verify setup
    echo "Verifying setup...\n";
    $tables = ['categories', 'products', 'modifiers', 'orders', 'users', 'settings'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "  ✓ $table: $count records\n";
    }
    
    echo "\n=== Setup Complete! ===\n";
    echo "You can now login to the admin panel:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n";
    echo "\nIMPORTANT: Change the default password after first login!\n\n";
    
    // Delete this file for security
    echo "Deleting setup file for security...\n";
    if (unlink(__FILE__)) {
        echo "✓ Setup file deleted successfully\n";
        echo "\nThis setup script has been removed and cannot be run again.\n";
    } else {
        echo "⚠ Warning: Could not delete setup.php - please remove it manually!\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nSetup failed. The setup.php file was NOT deleted.\n";
    echo "Fix the error and run setup again.\n";
    exit(1);
}
>>>>>>> cefe69b91ce1bc6cb9365cd2b54b22ef758174b3
