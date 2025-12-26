<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

// Check if current user is root/admin
$currentUserId = $_SESSION['admin_user_id'] ?? 0;
$sql = "SELECT role FROM users WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $currentUserId]);
$currentUser = $stmt->fetch();

if (!$currentUser || $currentUser['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Only root administrators can manage users']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'save':
            $data = $input['data'];
            $userId = $data['id'] ?? null;
            
            // Validate required fields
            if (empty($data['username'])) {
                throw new Exception('Username is required');
            }
            
            if (empty($userId) && empty($data['password'])) {
                throw new Exception('Password is required for new users');
            }
            
            if ($userId) {
                // Update existing user
                $sql = "UPDATE users SET username = :username, full_name = :full_name, 
                        role = :role, is_active = :is_active";
                $params = [
                    ':username' => $data['username'],
                    ':full_name' => $data['full_name'],
                    ':role' => $data['role'],
                    ':is_active' => $data['is_active'],
                    ':id' => $userId
                ];
                
                // Update password if provided
                if (!empty($data['password'])) {
                    $sql .= ", password = :password";
                    $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                }
                
                $sql .= " WHERE id = :id";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            } else {
                // Check if username already exists
                $sql = "SELECT id FROM users WHERE username = :username";
                $stmt = $db->prepare($sql);
                $stmt->execute([':username' => $data['username']]);
                
                if ($stmt->fetch()) {
                    throw new Exception('Username already exists');
                }
                
                // Create new user
                $sql = "INSERT INTO users (username, password, full_name, role, is_active) 
                        VALUES (:username, :password, :full_name, :role, :is_active)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':username' => $data['username'],
                    ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                    ':full_name' => $data['full_name'],
                    ':role' => $data['role'],
                    ':is_active' => $data['is_active']
                ]);
                
                echo json_encode(['success' => true, 'message' => 'User created successfully']);
            }
            break;
            
        case 'toggle_status':
            $userId = $input['id'];
            $status = $input['status'];
            
            // Prevent deactivating self
            if ($userId == $currentUserId) {
                throw new Exception('You cannot deactivate your own account');
            }
            
            $sql = "UPDATE users SET is_active = :status WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':status' => $status, ':id' => $userId]);
            
            echo json_encode(['success' => true, 'message' => 'User status updated']);
            break;
            
        case 'delete':
            $userId = $input['id'];
            
            // Prevent deleting self
            if ($userId == $currentUserId) {
                throw new Exception('You cannot delete your own account');
            }
            
            // Check if user exists
            $sql = "SELECT username FROM users WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Delete user
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $userId]);
            
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
