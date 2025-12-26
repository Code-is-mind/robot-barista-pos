<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

$reportType = $_GET['type'] ?? 'daily';
$date = $_GET['date'] ?? date('Y-m-d');
$month = $_GET['month'] ?? date('Y-m');

// Generate report based on type
if ($reportType === 'daily') {
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
    
} elseif ($reportType === 'weekly') {
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
    
    $sql = "SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_sales
            FROM orders 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND payment_status = 'Paid'";
    $stmt = $db->query($sql);
    $summary = $stmt->fetch();
    
} elseif ($reportType === 'monthly') {
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Robot Barista Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto lg:ml-0">
            <header class="bg-white shadow-sm p-4 pl-16 lg:pl-4">
                <h2 class="text-lg lg:text-2xl font-bold">Reports</h2>
            </header>

            <div class="p-6">
                <!-- Report Type Selection -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs lg:text-sm font-semibold mb-2">Report Type</label>
                            <select name="type" onchange="this.form.submit()" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="daily" <?= $reportType === 'daily' ? 'selected' : '' ?>>Daily Report</option>
                                <option value="weekly" <?= $reportType === 'weekly' ? 'selected' : '' ?>>Weekly Report</option>
                                <option value="monthly" <?= $reportType === 'monthly' ? 'selected' : '' ?>>Monthly Report</option>
                            </select>
                        </div>
                        <?php if ($reportType === 'daily'): ?>
                        <div>
                            <label class="block text-xs lg:text-sm font-semibold mb-2">Date</label>
                            <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <?php elseif ($reportType === 'monthly'): ?>
                        <div>
                            <label class="block text-xs lg:text-sm font-semibold mb-2">Month</label>
                            <input type="month" name="month" value="<?= htmlspecialchars($month) ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <?php endif; ?>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 text-sm">
                                <i class="fas fa-sync"></i> Generate
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <?php if (isset($summary)): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-500 text-sm">Total Orders</p>
                        <p class="text-3xl font-bold"><?= $summary['total_orders'] ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-500 text-sm">Total Sales</p>
                        <p class="text-3xl font-bold text-green-600">$<?= number_format($summary['total_sales'], 2) ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-500 text-sm">Average Order Value</p>
                        <p class="text-3xl font-bold">$<?= number_format($summary['avg_order_value'] ?? 0, 2) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Daily Report Details -->
                <?php if ($reportType === 'daily' && isset($topProducts)): ?>
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-bold mb-4">Payment Methods</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-orange-50 rounded-lg">
                            <p class="text-sm text-gray-600">KHQR Payments</p>
                            <p class="text-2xl font-bold text-orange-600">$<?= number_format($summary['khqr_sales'], 2) ?></p>
                        </div>
                        <div class="p-4 bg-green-50 rounded-lg">
                            <p class="text-sm text-gray-600">Cash Payments</p>
                            <p class="text-2xl font-bold text-green-600">$<?= number_format($summary['cash_sales'], 2) ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Top Products</h3>
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($topProducts as $product): ?>
                            <tr>
                                <td class="px-4 py-2"><?= htmlspecialchars($product['product_name']) ?></td>
                                <td class="px-4 py-2"><?= $product['quantity'] ?></td>
                                <td class="px-4 py-2">$<?= number_format($product['sales'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Weekly Report Details -->
                <?php if ($reportType === 'weekly' && isset($daily)): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Daily Breakdown</h3>
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($daily as $day): ?>
                            <tr>
                                <td class="px-4 py-2"><?= date('M d, Y', strtotime($day['date'])) ?></td>
                                <td class="px-4 py-2"><?= $day['orders'] ?></td>
                                <td class="px-4 py-2">$<?= number_format($day['sales'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Monthly Report Details -->
                <?php if ($reportType === 'monthly' && isset($byCategory)): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Sales by Category</h3>
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Items Sold</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($byCategory as $cat): ?>
                            <tr>
                                <td class="px-4 py-2 font-medium"><?= htmlspecialchars($cat['category']) ?></td>
                                <td class="px-4 py-2"><?= $cat['orders'] ?></td>
                                <td class="px-4 py-2"><?= $cat['items_sold'] ?></td>
                                <td class="px-4 py-2">$<?= number_format($cat['sales'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
