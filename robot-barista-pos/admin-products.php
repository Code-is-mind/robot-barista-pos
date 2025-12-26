<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Get all products
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.display_order, p.name";
        $stmt = $db->query($sql);
        $products = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $products
        ]);
        
    } elseif ($method === 'POST') {
        // Create product
        $input = json_decode(file_get_contents('php://input'), true);
        
        $sql = "INSERT INTO products (
                    category_id, name, description, image, 
                    price_usd, price_khr, is_available, display_order
                ) VALUES (
                    :category_id, :name, :description, :image,
                    :price_usd, :price_khr, :is_available, :display_order
                )";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':category_id' => $input['category_id'],
            ':name' => $input['name'],
            ':description' => $input['description'] ?? '',
            ':image' => $input['image'] ?? '',
            ':price_usd' => $input['price_usd'],
            ':price_khr' => $input['price_khr'],
            ':is_available' => $input['is_available'] ?? 1,
            ':display_order' => $input['display_order'] ?? 0
        ]);
        
        echo json_encode([
            'success' => true,
            'id' => $db->lastInsertId()
        ]);
        
    } elseif ($method === 'PUT') {
        // Update product
        $id = $_GET['id'] ?? null;
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$id) {
            throw new Exception('Product ID required');
        }
        
        $sql = "UPDATE products SET 
                category_id = :category_id,
                name = :name,
                description = :description,
                price_usd = :price_usd,
                price_khr = :price_khr,
                is_available = :is_available,
                display_order = :display_order";
        
        $params = [
            ':category_id' => $input['category_id'],
            ':name' => $input['name'],
            ':description' => $input['description'] ?? '',
            ':price_usd' => $input['price_usd'],
            ':price_khr' => $input['price_khr'],
            ':is_available' => $input['is_available'] ?? 1,
            ':display_order' => $input['display_order'] ?? 0,
            ':id' => $id
        ];
        
        if (isset($input['image'])) {
            $sql .= ", image = :image";
            $params[':image'] = $input['image'];
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true]);
        
    } elseif ($method === 'DELETE') {
        // Delete product
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Product ID required');
        }
        
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        echo json_encode(['success' => true]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
