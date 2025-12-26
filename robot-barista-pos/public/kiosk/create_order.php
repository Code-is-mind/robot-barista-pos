<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

$input = json_decode(file_get_contents('php://input'), true);

try {
    // Generate order number
    $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Get exchange rate from settings
    $sql = "SELECT setting_value FROM settings WHERE setting_key = 'exchange_rate_usd_to_khr'";
    $stmt = $db->query($sql);
    $exchangeRate = $stmt->fetchColumn() ?: 4100;
    
    // Determine if customer paid in KHR
    $paidCurrency = $input['currency'];
    $paidAmount = $input['total'];
    
    // Convert everything to USD for storage
    if ($paidCurrency === 'KHR') {
        $subtotalUSD = $input['subtotal'] / $exchangeRate;
        $taxUSD = $input['tax'] / $exchangeRate;
        $totalUSD = $input['total'] / $exchangeRate;
        $unitPriceUSD = $input['unit_price'] / $exchangeRate;
        
        // Create payment note
        $paymentNote = "Customer paid in KHR: ៛" . number_format($paidAmount, 0) . " (Exchange rate: " . number_format($exchangeRate, 0) . ")";
    } else {
        // Already in USD
        $subtotalUSD = $input['subtotal'];
        $taxUSD = $input['tax'];
        $totalUSD = $input['total'];
        $unitPriceUSD = $input['unit_price'];
        $paymentNote = "Customer paid in USD: $" . number_format($paidAmount, 2);
    }
    
    // Add MD5 hash to note for transaction tracking
    if (!empty($input['md5_hash'])) {
        $paymentNote .= " | MD5: " . $input['md5_hash'];
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Insert order (always store in USD)
    $sql = "INSERT INTO orders (
        order_number, customer_name, currency, subtotal, tax_amount, 
        total_amount, payment_method, payment_status, order_status, notes
    ) VALUES (
        :order_number, :customer_name, 'USD', :subtotal, :tax_amount,
        :total_amount, 'KHQR', 'Pending', 'Pending', :notes
    )";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':order_number' => $orderNumber,
        ':customer_name' => $input['customer_name'],
        ':subtotal' => $subtotalUSD,
        ':tax_amount' => $taxUSD,
        ':total_amount' => $totalUSD,
        ':notes' => $paymentNote
    ]);
    
    $orderId = $db->lastInsertId();
    
    // Insert order item (in USD)
    $sql = "INSERT INTO order_items (
        order_id, product_id, product_name, quantity, 
        unit_price, modifiers_json, subtotal
    ) VALUES (
        :order_id, :product_id, :product_name, :quantity,
        :unit_price, :modifiers_json, :subtotal
    )";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':order_id' => $orderId,
        ':product_id' => $input['product_id'],
        ':product_name' => $input['product_name'],
        ':quantity' => $input['quantity'],
        ':unit_price' => $unitPriceUSD,
        ':modifiers_json' => json_encode(['size' => $input['size']]),
        ':subtotal' => $subtotalUSD
    ]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'stored_in_usd' => true,
        'paid_currency' => $paidCurrency,
        'paid_amount' => $paidAmount,
        'usd_amount' => $totalUSD
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
