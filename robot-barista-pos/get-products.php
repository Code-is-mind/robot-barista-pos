<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get category filter if provided
    $categoryId = $_GET['category_id'] ?? null;
    
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_available = 1";
    
    if ($categoryId) {
        $sql .= " AND p.category_id = :category_id";
    }
    
    $sql .= " ORDER BY p.display_order, p.name";
    
    $stmt = $db->prepare($sql);
    
    if ($categoryId) {
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $products
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
