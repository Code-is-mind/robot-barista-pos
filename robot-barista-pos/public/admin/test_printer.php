<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get settings
    $sql = "SELECT * FROM settings";
    $stmt = $db->query($sql);
    $settingsData = $stmt->fetchAll();
    $settings = [];
    foreach ($settingsData as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
    
    $printerIP = $settings['printer_ip'] ?? '192.168.1.100';
    $printerPort = $settings['printer_port'] ?? '9100';
    
    // Test content
    $testContent = "\x1B\x40"; // Initialize
    $testContent .= "\x1B\x61\x01"; // Center align
    $testContent .= "\x1B\x21\x30"; // Double size
    $testContent .= "PRINTER TEST\n";
    $testContent .= "\x1B\x21\x00"; // Normal size
    $testContent .= "\x1B\x61\x00"; // Left align
    $testContent .= str_repeat("-", 32) . "\n";
    $testContent .= "This is a test print\n";
    $testContent .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $testContent .= "IP: " . $printerIP . "\n";
    $testContent .= "Port: " . $printerPort . "\n";
    $testContent .= str_repeat("-", 32) . "\n";
    $testContent .= "\x1B\x61\x01"; // Center
    $testContent .= "Test Successful!\n\n\n";
    $testContent .= "\x1D\x56\x00"; // Cut
    
    // Check if sockets extension is available
    if (!function_exists('socket_create')) {
        // Use fsockopen as fallback
        $fp = @fsockopen($printerIP, $printerPort, $errno, $errstr, 2);
        if (!$fp) {
            $result = ['success' => false, 'message' => 'Failed to connect to printer at ' . $printerIP . ':' . $printerPort . ' - ' . $errstr];
        } else {
            $written = fwrite($fp, $testContent);
            if ($written === false) {
                $result = ['success' => false, 'message' => 'Connected but failed to send data'];
            } else {
                $result = ['success' => true, 'message' => 'Test print sent successfully! Check your printer.'];
            }
            fclose($fp);
        }
    } else {
        // Use sockets extension
        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            $result = ['success' => false, 'message' => 'Failed to create socket'];
        } else {
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 0]);
            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 2, 'usec' => 0]);
            
            $connected = @socket_connect($socket, $printerIP, $printerPort);
            if ($connected === false) {
                $result = ['success' => false, 'message' => 'Failed to connect to printer at ' . $printerIP . ':' . $printerPort];
            } else {
                $written = @socket_write($socket, $testContent, strlen($testContent));
                if ($written === false) {
                    $result = ['success' => false, 'message' => 'Connected but failed to send data'];
                } else {
                    $result = ['success' => true, 'message' => 'Test print sent successfully! Check your printer.'];
                }
            }
            socket_close($socket);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Printer - Robot Barista Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto lg:ml-0">
            <header class="bg-white shadow-sm p-4 pl-16 lg:pl-4">
                <h2 class="text-lg lg:text-2xl font-bold">Test Printer</h2>
            </header>

            <div class="p-4 lg:p-6 max-w-2xl">
                <?php if ($result): ?>
                <div class="mb-4 p-4 rounded-lg <?= $result['success'] ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700' ?>">
                    <i class="fas fa-<?= $result['success'] ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($result['message']) ?>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-bold mb-4">
                        <i class="fas fa-print text-purple-600"></i> Printer Connection Test
                    </h3>
                    
                    <p class="text-gray-600 mb-4">
                        This will send a test print to your configured thermal printer. 
                        Make sure your printer is powered on and connected to the network.
                    </p>
                    
                    <form method="POST">
                        <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700">
                            <i class="fas fa-print"></i> Send Test Print
                        </button>
                    </form>
                    
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold mb-2">Troubleshooting:</h4>
                        <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                            <li>Verify printer IP address in Settings</li>
                            <li>Check if printer is on the same network</li>
                            <li>Ensure port 9100 is not blocked by firewall</li>
                            <li>Try pinging the printer IP from command line</li>
                            <li>Check printer supports ESC/POS commands</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="settings.php" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-cog"></i> Go to Printer Settings
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
