<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

$orderId = $_POST['order_id'] ?? null;

if (!$orderId) {
    echo json_encode(['success' => false, 'error' => 'Order ID required']);
    exit;
}

try {
    // Get order
    $sql = "SELECT * FROM orders WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }
    
    // Get order items
    $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':order_id' => $orderId]);
    $items = $stmt->fetchAll();
    
    // Get settings
    $sql = "SELECT * FROM settings";
    $stmt = $db->query($sql);
    $settingsData = $stmt->fetchAll();
    $settings = [];
    foreach ($settingsData as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
    
    // Build receipt content
    $symbol = $order['currency'] === 'USD' ? '$' : '៛';
    $businessName = $settings['business_name'] ?? 'Robot Barista';
    $businessAddress = $settings['business_address'] ?? '';
    $businessPhone = $settings['business_phone'] ?? '';
    
    $receiptContent = generateReceiptContent($order, $items, $symbol, $businessName, $businessAddress, $businessPhone);
    
    // Get printer settings
    $printerIP = $settings['printer_ip'] ?? '192.168.1.100';
    $printerPort = $settings['printer_port'] ?? '9100';
    $printerEnabled = $settings['printer_enabled'] ?? '1';
    
    if ($printerEnabled == '1') {
        // Try to send to network printer
        $result = sendToPrinter($printerIP, $printerPort, $receiptContent);
        
        if ($result) {
            // Log successful print
            $sql = "INSERT INTO print_logs (order_id, print_type, print_status) VALUES (:order_id, 'receipt', 'success')";
            $stmt = $db->prepare($sql);
            $stmt->execute([':order_id' => $orderId]);
            
            // Update order
            $sql = "UPDATE orders SET receipt_printed = 1 WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $orderId]);
            
            echo json_encode(['success' => true, 'message' => 'Receipt printed successfully']);
        } else {
            // Log failed print
            $sql = "INSERT INTO print_logs (order_id, print_type, print_status, error_message) VALUES (:order_id, 'receipt', 'failed', :error)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':order_id' => $orderId,
                ':error' => 'Failed to connect to printer'
            ]);
            
            echo json_encode(['success' => false, 'error' => 'Printer connection failed', 'fallback' => true]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Printer disabled', 'fallback' => true]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function generateReceiptContent($order, $items, $symbol, $businessName, $businessAddress, $businessPhone) {
    $content = "\x1B\x40"; // Initialize printer
    $content .= "\x1B\x61\x01"; // Center align
    $content .= "\x1B\x21\x30"; // Double height + width
    $content .= $businessName . "\n";
    $content .= "\x1B\x21\x00"; // Normal size
    
    if ($businessAddress) {
        $content .= $businessAddress . "\n";
    }
    if ($businessPhone) {
        $content .= "Tel: " . $businessPhone . "\n";
    }
    
    $content .= "Self-Service Kiosk\n";
    $content .= str_repeat("-", 32) . "\n";
    
    $content .= "\x1B\x61\x00"; // Left align
    $content .= "Order #: " . $order['order_number'] . "\n";
    $content .= "Date: " . date('d/m/Y H:i', strtotime($order['created_at'])) . "\n";
    $content .= "Customer: " . $order['customer_name'] . "\n";
    $content .= str_repeat("-", 32) . "\n";
    
    foreach ($items as $item) {
        $modifiers = json_decode($item['modifiers_json'], true);
        $content .= $item['product_name'] . "\n";
        $content .= "  " . ($modifiers['size'] ?? 'Regular') . " x" . $item['quantity'];
        $content .= str_repeat(" ", 20 - strlen($modifiers['size'] ?? 'Regular') - strlen($item['quantity']));
        $content .= $symbol . number_format($item['subtotal'], 2) . "\n";
    }
    
    $content .= str_repeat("-", 32) . "\n";
    $content .= "Subtotal:" . str_repeat(" ", 15) . $symbol . number_format($order['subtotal'], 2) . "\n";
    $content .= "Tax (10%):" . str_repeat(" ", 14) . $symbol . number_format($order['tax_amount'], 2) . "\n";
    
    $content .= "\x1B\x21\x30"; // Double height + width
    $content .= "TOTAL:" . str_repeat(" ", 10) . $symbol . number_format($order['total_amount'], 2) . "\n";
    $content .= "\x1B\x21\x00"; // Normal size
    
    $content .= str_repeat("-", 32) . "\n";
    $content .= "Payment: " . $order['payment_method'] . "\n";
    $content .= "Status: " . $order['payment_status'] . "\n";
    $content .= str_repeat("-", 32) . "\n";
    
    $content .= "\x1B\x61\x01"; // Center align
    $content .= "\nThank you for your order!\n";
    $content .= "Enjoy your drink!\n\n\n";
    
    $content .= "\x1D\x56\x00"; // Cut paper
    
    return $content;
}

function sendToPrinter($ip, $port, $content) {
    // Check if sockets extension is available
    if (!function_exists('socket_create')) {
        // Use fsockopen as fallback
        $fp = @fsockopen($ip, $port, $errno, $errstr, 2);
        if (!$fp) {
            return false;
        }
        
        $written = fwrite($fp, $content);
        fclose($fp);
        
        return $written !== false;
    }
    
    // Use sockets extension
    $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket === false) {
        return false;
    }
    
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 2, 'usec' => 0]);
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 2, 'usec' => 0]);
    
    $result = @socket_connect($socket, $ip, $port);
    if ($result === false) {
        socket_close($socket);
        return false;
    }
    
    $written = @socket_write($socket, $content, strlen($content));
    socket_close($socket);
    
    return $written !== false;
}
