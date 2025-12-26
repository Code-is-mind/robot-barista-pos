<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $orderId = $_GET['id'] ?? null;
    
    if (!$orderId) {
        throw new Exception('Order ID required');
    }
    
    // Get order
    $sql = "SELECT * FROM orders WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Get order items
    $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':order_id' => $orderId]);
    $items = $stmt->fetchAll();
    
    $order['items'] = $items;
    
    echo json_encode([
        'success' => true,
        'data' => $order
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
