<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Debug: Log the input
    error_log("Login attempt - Input: " . print_r($input, true));
    
    if (!$input || !isset($input['username']) || !isset($input['password'])) {
        throw new Exception('Username and password required');
    }
    
    $username = $input['username'];
    $password = $input['password'];
    
    // Debug: Log username
    error_log("Login attempt for username: " . $username);
    
    // Get user from database
    $sql = "SELECT * FROM users WHERE username = :username AND is_active = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
    
    // Debug: Log if user found
    error_log("User found: " . ($user ? "YES" : "NO"));
    
    if (!$user) {
        // Check if user exists but is inactive
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $db->prepare($sql);
        $stmt->execute([':username' => $username]);
        $anyUser = $stmt->fetch();
        
        if ($anyUser && $anyUser['is_active'] == 0) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'User account is inactive'
            ]);
            exit;
        }
        
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid username or password',
            'debug' => 'User not found in database'
        ]);
        exit;
    }
    
    // Verify password
    $passwordMatch = password_verify($password, $user['password']);
    error_log("Password match: " . ($passwordMatch ? "YES" : "NO"));
    
    if (!$passwordMatch) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid username or password',
            'debug' => 'Password verification failed'
        ]);
        exit;
    }
    
    // Set session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_role'] = $user['role'];
    
    // Update last login
    $sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $user['id']]);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'username' => $user['username'],
            'full_name' => $user['full_name'] ?? $user['username'],
            'role' => $user['role'] ?? 'admin'
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
