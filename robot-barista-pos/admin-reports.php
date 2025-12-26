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
    
    $type = $_GET['type'] ?? 'daily';
    $date = $_GET['date'] ?? date('Y-m-d');
    
    if ($type === 'daily') {
        // Daily report
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_sales,
                    COALESCE(AVG(total_amount), 0) as avg_order_value,
                    SUM(CASE WHEN payment_method = 'KHQR' THEN total_amount ELSE 0 END) as khqr_sales,
                    SUM(CASE WHEN payment_method = 'Cash' THEN total_amount ELSE 0 END) as cash_sales
                FROM orders 
                WHERE DATE(created_at) = :date 
                AND payment_status = 'Paid'";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([':date' => $date]);
        $summary = $stmt->fetch();
        
        // Top products
        $sql = "SELECT 
                    oi.product_name,
                    SUM(oi.quantity) as quantity,
                    SUM(oi.subtotal) as sales
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE DATE(o.created_at) = :date
                AND o.payment_status = 'Paid'
                GROUP BY oi.product_name
                ORDER BY quantity DESC
                LIMIT 10";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([':date' => $date]);
        $topProducts = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'top_products' => $topProducts
            ]
        ]);
        
    } elseif ($type === 'weekly') {
        // Weekly report (last 7 days)
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as orders,
                    COALESCE(SUM(total_amount), 0) as sales
                FROM orders 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND payment_status = 'Paid'
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        $stmt = $db->query($sql);
        $daily = $stmt->fetchAll();
        
        // Summary
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_sales
                FROM orders 
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND payment_status = 'Paid'";
        
        $stmt = $db->query($sql);
        $summary = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'daily' => $daily
            ]
        ]);
        
    } elseif ($type === 'monthly') {
        // Monthly report
        $month = $_GET['month'] ?? date('Y-m');
        
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_sales,
                    COALESCE(AVG(total_amount), 0) as avg_order_value
                FROM orders 
                WHERE DATE_FORMAT(created_at, '%Y-%m') = :month
                AND payment_status = 'Paid'";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([':month' => $month]);
        $summary = $stmt->fetch();
        
        // Sales by category
        $sql = "SELECT 
                    c.name as category,
                    COUNT(DISTINCT o.id) as orders,
                    SUM(oi.quantity) as items_sold,
                    SUM(oi.subtotal) as sales
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN products p ON oi.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                WHERE DATE_FORMAT(o.created_at, '%Y-%m') = :month
                AND o.payment_status = 'Paid'
                GROUP BY c.id, c.name
                ORDER BY sales DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([':month' => $month]);
        $byCategory = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'by_category' => $byCategory
            ]
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
