<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Generate order number
    $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Insert order
    $sql = "INSERT INTO orders (
        order_number, customer_name, currency, subtotal, tax_amount, 
        total_amount, payment_method, payment_status, order_status
    ) VALUES (
        :order_number, :customer_name, :currency, :subtotal, :tax_amount,
        :total_amount, :payment_method, 'Pending', 'Pending'
    )";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':order_number' => $orderNumber,
        ':customer_name' => $input['customer_name'] ?? 'Walk-In Customer',
        ':currency' => $input['currency'] ?? 'USD',
        ':subtotal' => $input['subtotal'],
        ':tax_amount' => $input['tax_amount'],
        ':total_amount' => $input['total_amount'],
        ':payment_method' => $input['payment_method'] ?? 'KHQR'
    ]);
    
    $orderId = $db->lastInsertId();
    
    // Insert order items
    $sql = "INSERT INTO order_items (
        order_id, product_id, product_name, quantity, 
        unit_price, modifiers_json, subtotal
    ) VALUES (
        :order_id, :product_id, :product_name, :quantity,
        :unit_price, :modifiers_json, :subtotal
    )";
    
    $stmt = $db->prepare($sql);
    
    foreach ($input['items'] as $item) {
        $stmt->execute([
            ':order_id' => $orderId,
            ':product_id' => $item['product_id'],
            ':product_name' => $item['product_name'],
            ':quantity' => $item['quantity'],
            ':unit_price' => $item['unit_price'],
            ':modifiers_json' => json_encode($item['modifiers'] ?? []),
            ':subtotal' => $item['subtotal']
        ]);
    }
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'order_id' => $orderId,
            'order_number' => $orderNumber
        ]
    ]);
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
