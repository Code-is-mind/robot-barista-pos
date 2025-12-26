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
        // Get all settings
        $sql = "SELECT * FROM settings ORDER BY setting_key";
        $stmt = $db->query($sql);
        $settings = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $settings
        ]);
        
    } elseif ($method === 'PUT') {
        // Update setting
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['setting_key']) || !isset($input['setting_value'])) {
            throw new Exception('Setting key and value required');
        }
        
        $sql = "UPDATE settings 
                SET setting_value = :value 
                WHERE setting_key = :key";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':key' => $input['setting_key'],
            ':value' => $input['setting_value']
        ]);
        
        echo json_encode(['success' => true]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
