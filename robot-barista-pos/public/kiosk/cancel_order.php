<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

$orderId = $_POST['order_id'] ?? null;

if ($orderId) {
    try {
        // Update order status to Cancelled
        $sql = "UPDATE orders SET payment_status = 'Cancelled', order_status = 'Cancelled' WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $orderId]);
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Order ID required']);
}
