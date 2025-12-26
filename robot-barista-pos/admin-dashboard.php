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
    
    // Today's sales
    $sql = "SELECT 
                COUNT(*) as order_count,
                COALESCE(SUM(total_amount), 0) as total_sales,
                COALESCE(AVG(total_amount), 0) as avg_order_value
            FROM orders 
            WHERE DATE(created_at) = CURDATE() 
            AND payment_status = 'Paid'";
    $stmt = $db->query($sql);
    $todayStats = $stmt->fetch();
    
    // Total products
    $sql = "SELECT COUNT(*) as count FROM products WHERE is_available = 1";
    $stmt = $db->query($sql);
    $productCount = $stmt->fetch()['count'];
    
    // Last 7 days sales
    $sql = "SELECT 
                DATE(created_at) as date,
                COALESCE(SUM(total_amount), 0) as total
            FROM orders 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND payment_status = 'Paid'
            GROUP BY DATE(created_at)
            ORDER BY date ASC";
    $stmt = $db->query($sql);
    $salesTrend = $stmt->fetchAll();
    
    // Top products
    $sql = "SELECT 
                oi.product_name,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.subtotal) as total_sales
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND o.payment_status = 'Paid'
            GROUP BY oi.product_name
            ORDER BY total_quantity DESC
            LIMIT 5";
    $stmt = $db->query($sql);
    $topProducts = $stmt->fetchAll();
    
    // Recent orders
    $sql = "SELECT * FROM orders 
            ORDER BY created_at DESC 
            LIMIT 10";
    $stmt = $db->query($sql);
    $recentOrders = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'today_sales' => $todayStats['total_sales'],
            'today_orders' => $todayStats['order_count'],
            'total_products' => $productCount,
            'avg_order_value' => $todayStats['avg_order_value'],
            'sales_trend' => $salesTrend,
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
