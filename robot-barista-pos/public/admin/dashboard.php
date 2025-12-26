<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

// Get today's statistics
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
$sql = "SELECT * FROM orders ORDER BY created_at DESC LIMIT 10";
$stmt = $db->query($sql);
$recentOrders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Robot Barista Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto lg:ml-0">
            <header class="bg-white shadow-sm p-4 pl-16 lg:pl-4">
                <h2 class="text-xl lg:text-2xl font-bold">Dashboard Overview</h2>
            </header>

            <div class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Today's Sales</p>
                                <p class="text-2xl font-bold">$<?= number_format($todayStats['total_sales'], 2) ?></p>
                            </div>
                            <i class="fas fa-dollar-sign text-4xl text-green-500"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Today's Orders</p>
                                <p class="text-2xl font-bold"><?= $todayStats['order_count'] ?></p>
                            </div>
                            <i class="fas fa-shopping-cart text-4xl text-blue-500"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Total Products</p>
                                <p class="text-2xl font-bold"><?= $productCount ?></p>
                            </div>
                            <i class="fas fa-coffee text-4xl text-orange-500"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Avg Order Value</p>
                                <p class="text-2xl font-bold">$<?= number_format($todayStats['avg_order_value'], 2) ?></p>
                            </div>
                            <i class="fas fa-chart-line text-4xl text-purple-500"></i>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold mb-4">Sales Trend (Last 7 Days)</h3>
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold mb-4">Top Products</h3>
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 lg:p-6">
                        <h3 class="text-lg font-bold mb-4">Recent Orders</h3>
                        <div class="overflow-x-auto -mx-4 lg:mx-0">
                            <table class="w-full min-w-[600px]">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                        <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                        <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-2 lg:px-4 py-3 text-sm"><?= htmlspecialchars($order['order_number']) ?></td>
                                        <td class="px-2 lg:px-4 py-3 text-sm"><?= htmlspecialchars($order['customer_name']) ?></td>
                                        <td class="px-2 lg:px-4 py-3 text-sm">$<?= number_format($order['total_amount'], 2) ?></td>
                                        <td class="px-2 lg:px-4 py-3">
                                            <span class="px-2 py-1 rounded text-xs <?= $order['payment_status'] === 'Paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                <?= htmlspecialchars($order['payment_status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-2 lg:px-4 py-3 text-xs text-gray-600 hidden sm:table-cell">
                                            <?= date('M d, Y H:i', strtotime($order['created_at'])) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sales Trend Chart
        const salesCtx = document.getElementById('salesTrendChart');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($salesTrend, 'date')) ?>,
                datasets: [{
                    label: 'Sales',
                    data: <?= json_encode(array_column($salesTrend, 'total')) ?>,
                    borderColor: 'rgb(249, 115, 22)',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => '$' + value }
                    }
                }
            }
        });

        // Top Products Chart
        const productsCtx = document.getElementById('topProductsChart');
        new Chart(productsCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($topProducts, 'product_name')) ?>,
                datasets: [{
                    label: 'Quantity Sold',
                    data: <?= json_encode(array_column($topProducts, 'total_quantity')) ?>,
                    backgroundColor: 'rgba(249, 115, 22, 0.8)'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>
