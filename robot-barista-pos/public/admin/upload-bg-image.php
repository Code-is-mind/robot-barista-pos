<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle image upload
        if (isset($_FILES['bg_image']) && $_FILES['bg_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['bg_image'];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP images are allowed.');
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('File too large. Maximum size is 5MB.');
            }
            
            // Create uploads directory if it doesn't exist
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'bg_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('Failed to upload file');
            }
            
            // Get old background image to delete
            $sql = "SELECT setting_value FROM settings WHERE setting_key = 'ui_bg_image'";
            $stmt = $db->query($sql);
            $result = $stmt->fetch();
            $oldImage = $result['setting_value'] ?? '';
            
            // Delete old image if exists
            if (!empty($oldImage) && file_exists($uploadDir . $oldImage)) {
                unlink($uploadDir . $oldImage);
            }
            
            // Update database
            $sql = "INSERT INTO settings (setting_key, setting_value) 
                    VALUES ('ui_bg_image', :filename)
                    ON DUPLICATE KEY UPDATE setting_value = :filename";
            $stmt = $db->prepare($sql);
            $stmt->execute([':filename' => $filename]);
            
            echo json_encode([
                'success' => true,
                'filename' => $filename,
                'url' => '../uploads/' . $filename
            ]);
            
        } else {
            throw new Exception('No file uploaded or upload error');
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Handle image deletion
        $sql = "SELECT setting_value FROM settings WHERE setting_key = 'ui_bg_image'";
        $stmt = $db->query($sql);
        $result = $stmt->fetch();
        $oldImage = $result['setting_value'] ?? '';
        
        // Delete file if exists
        if (!empty($oldImage)) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (file_exists($uploadDir . $oldImage)) {
                unlink($uploadDir . $oldImage);
            }
        }
        
        // Clear database setting
        $sql = "UPDATE settings SET setting_value = '' WHERE setting_key = 'ui_bg_image'";
        $db->query($sql);
        
        echo json_encode(['success' => true, 'message' => 'Background image removed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
