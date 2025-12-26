<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get filters
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    $paymentMethod = $_GET['payment_method'] ?? null;
    
    $sql = "SELECT * FROM orders WHERE 1=1";
    $params = [];
    
    if ($dateFrom) {
        $sql .= " AND DATE(created_at) >= :date_from";
        $params[':date_from'] = $dateFrom;
    }
    
    if ($dateTo) {
        $sql .= " AND DATE(created_at) <= :date_to";
        $params[':date_to'] = $dateTo;
    }
    
    if ($paymentMethod) {
        $sql .= " AND payment_method = :payment_method";
        $params[':payment_method'] = $paymentMethod;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $orders
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
