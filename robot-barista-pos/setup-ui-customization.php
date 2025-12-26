<?php
/**
 * Setup Script for UI Customization
 * Run this file once to add the new features to your database
 */

require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup UI Customization</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; }
        h1 { color: #333; }
        .step { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff; }
    </style>
</head>
<body>
    <h1>🎨 UI Customization Setup</h1>
    <p>This script will add the new UI customization features to your database.</p>
";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<div class='step'><strong>Step 1:</strong> Checking database connection...</div>";
    echo "<div class='success'>✓ Database connection successful!</div>";
    
    echo "<div class='step'><strong>Step 2:</strong> Adding new settings...</div>";
    
    // Add UI customization settings
    $settings = [
        'ui_navbar_color' => '#16a34a',
        'ui_bg_color' => '#f3f4f6',
        'ui_primary_color' => '#16a34a',
        'ui_bg_image' => ''
    ];
    
    $added = 0;
    $existing = 0;
    
    foreach ($settings as $key => $value) {
        // Check if setting exists
        $sql = "SELECT COUNT(*) as count FROM settings WHERE setting_key = :key";
        $stmt = $db->prepare($sql);
        $stmt->execute([':key' => $key]);
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            // Insert new setting
            $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)";
            $stmt = $db->prepare($sql);
            $stmt->execute([':key' => $key, ':value' => $value]);
            echo "<div class='success'>✓ Added setting: <strong>$key</strong></div>";
            $added++;
        } else {
            echo "<div class='info'>ℹ Setting already exists: <strong>$key</strong></div>";
            $existing++;
        }
    }
    
    echo "<div class='step'><strong>Step 3:</strong> Verifying business name...</div>";
    
    // Check if business_name exists
    $sql = "SELECT setting_value FROM settings WHERE setting_key = 'business_name'";
    $stmt = $db->query($sql);
    $result = $stmt->fetch();
    
    if (!$result || empty($result['setting_value'])) {
        $sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('business_name', 'Robot Barista Cafe')
                ON DUPLICATE KEY UPDATE setting_value = 'Robot Barista Cafe'";
        $db->query($sql);
        echo "<div class='success'>✓ Set default business name</div>";
    } else {
        echo "<div class='info'>ℹ Business name already set: <strong>" . htmlspecialchars($result['setting_value']) . "</strong></div>";
    }
    
    echo "<div class='step'><strong>Summary:</strong></div>";
    echo "<div class='success'>";
    echo "<h3>✓ Setup Complete!</h3>";
    echo "<ul>";
    echo "<li>New settings added: <strong>$added</strong></li>";
    echo "<li>Existing settings: <strong>$existing</strong></li>";
    echo "<li>Total settings: <strong>" . ($added + $existing) . "</strong></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>📋 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Go to <strong>Admin Panel → Settings</strong></li>";
    echo "<li>Scroll to <strong>Kiosk UI Customization</strong> section</li>";
    echo "<li>Customize your colors and business name</li>";
    echo "<li>Save settings and test on the kiosk</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>📖 Documentation:</h3>";
    echo "<p>For detailed information, see <strong>UI_CUSTOMIZATION_GUIDE.md</strong></p>";
    echo "</div>";
    
    echo "<div style='margin-top: 30px; text-align: center;'>";
    echo "<a href='public/admin/settings.php' style='display: inline-block; padding: 15px 30px; background: #16a34a; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Go to Admin Settings →</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>✗ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Check your database connection in <code>config/database.php</code></li>";
    echo "<li>Ensure the database exists and is accessible</li>";
    echo "<li>Verify the settings table exists (run main schema.sql first)</li>";
    echo "</ul>";
    echo "</div>";
}

echo "</body></html>";
