<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    $orderId = $_GET['id'] ?? null;
    
    if (!$orderId || !$input) {
        throw new Exception('Invalid input data');
    }
    
    // Update payment status
    if (isset($input['payment_status'])) {
        $sql = "UPDATE orders SET payment_status = :payment_status WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':payment_status' => $input['payment_status'],
            ':id' => $orderId
        ]);
    }
    
    // Update order status
    if (isset($input['order_status'])) {
        $sql = "UPDATE orders SET order_status = :order_status WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':order_status' => $input['order_status'],
            ':id' => $orderId
        ]);
    }
    
    echo json_encode([
        'success' => true
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
