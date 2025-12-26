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
        // Get all categories
        $sql = "SELECT * FROM categories ORDER BY display_order, name";
        $stmt = $db->query($sql);
        $categories = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);
        
    } elseif ($method === 'POST') {
        // Create category
        $input = json_decode(file_get_contents('php://input'), true);
        
        $sql = "INSERT INTO categories (name, description, icon, display_order, is_active) 
                VALUES (:name, :description, :icon, :display_order, :is_active)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':name' => $input['name'],
            ':description' => $input['description'] ?? '',
            ':icon' => $input['icon'] ?? 'fa-coffee',
            ':display_order' => $input['display_order'] ?? 0,
            ':is_active' => $input['is_active'] ?? 1
        ]);
        
        echo json_encode([
            'success' => true,
            'id' => $db->lastInsertId()
        ]);
        
    } elseif ($method === 'PUT') {
        // Update category
        $id = $_GET['id'] ?? null;
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$id) {
            throw new Exception('Category ID required');
        }
        
        $sql = "UPDATE categories SET 
                name = :name,
                description = :description,
                icon = :icon,
                display_order = :display_order,
                is_active = :is_active
                WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':name' => $input['name'],
            ':description' => $input['description'] ?? '',
            ':icon' => $input['icon'] ?? 'fa-coffee',
            ':display_order' => $input['display_order'] ?? 0,
            ':is_active' => $input['is_active'] ?? 1,
            ':id' => $id
        ]);
        
        echo json_encode(['success' => true]);
        
    } elseif ($method === 'DELETE') {
        // Delete category
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Category ID required');
        }
        
        $sql = "DELETE FROM categories WHERE id = :id";
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
