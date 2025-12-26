<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

// Get filter parameters
$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$paymentMethod = $_GET['payment_method'] ?? '';

// Build query
$sql = "SELECT * FROM orders WHERE DATE(created_at) BETWEEN :date_from AND :date_to";
$params = [':date_from' => $dateFrom, ':date_to' => $dateTo];

if ($paymentMethod) {
    $sql .= " AND payment_method = :payment_method";
    $params[':payment_method'] = $paymentMethod;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Robot Barista Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto lg:ml-0">
            <header class="bg-white shadow-sm p-4 pl-16 lg:pl-4">
                <h2 class="text-lg lg:text-2xl font-bold">Orders</h2>
            </header>

            <div class="p-6">
                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-4 mb-6">
                    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs lg:text-sm font-semibold mb-2">From Date</label>
                            <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs lg:text-sm font-semibold mb-2">To Date</label>
                            <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs lg:text-sm font-semibold mb-2">Payment Method</label>
                            <select name="payment_method" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="">All</option>
                                <option value="KHQR" <?= $paymentMethod === 'KHQR' ? 'selected' : '' ?>>KHQR</option>
                                <option value="Cash" <?= $paymentMethod === 'Cash' ? 'selected' : '' ?>>Cash</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 text-sm">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Orders Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto -mx-4 lg:mx-0">
                        <table class="w-full min-w-[700px]">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Customer</th>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Payment</th>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Date</th>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">No orders found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 lg:px-4 py-3 font-medium text-sm"><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td class="px-2 lg:px-4 py-3 text-sm hidden sm:table-cell"><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td class="px-2 lg:px-4 py-3 text-sm">$<?= number_format($order['total_amount'], 2) ?></td>
                                    <td class="px-2 lg:px-4 py-3 hidden md:table-cell">
                                        <span class="px-2 py-1 rounded text-xs <?= $order['payment_method'] === 'KHQR' ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= htmlspecialchars($order['payment_method']) ?>
                                        </span>
                                    </td>
                                    <td class="px-2 lg:px-4 py-3">
                                        <span class="px-2 py-1 rounded text-xs <?= $order['payment_status'] === 'Paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                            <?= htmlspecialchars($order['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-2 lg:px-4 py-3 text-xs hidden lg:table-cell"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td class="px-2 lg:px-4 py-3">
                                        <button onclick="viewOrderDetails(<?= $order['id'] ?>)" class="text-blue-600 hover:text-blue-800 mr-3" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="../../print-receipt.php?order_id=<?= $order['id'] ?>" target="_blank" class="text-blue-600 hover:text-blue-800" title="Print Receipt">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary -->
                <?php if (!empty($orders)): 
                    $totalSales = array_sum(array_column($orders, 'total_amount'));
                    $totalOrders = count($orders);
                    $avgOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
                ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-500 text-sm">Total Orders</p>
                        <p class="text-2xl font-bold"><?= $totalOrders ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-500 text-sm">Total Sales</p>
                        <p class="text-2xl font-bold">$<?= number_format($totalSales, 2) ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-500 text-sm">Average Order</p>
                        <p class="text-2xl font-bold">$<?= number_format($avgOrder, 2) ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold">Order Details</h3>
                <button onclick="closeOrderDetails()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <div id="orderDetailsContent" class="p-6">
                <div class="flex justify-center items-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewOrderDetails(orderId) {
            // Show modal
            document.getElementById('orderDetailsModal').classList.remove('hidden');
            
            // Show loading
            document.getElementById('orderDetailsContent').innerHTML = `
                <div class="flex justify-center items-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
                </div>
            `;
            
            // Fetch order details
            fetch('get_order_details.php?id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrderDetails(data.order, data.items);
                    } else {
                        document.getElementById('orderDetailsContent').innerHTML = `
                            <div class="text-center py-8 text-red-600">
                                <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                                <p>Error loading order details</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('orderDetailsContent').innerHTML = `
                        <div class="text-center py-8 text-red-600">
                            <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                            <p>Error loading order details</p>
                        </div>
                    `;
                });
        }
        
        function displayOrderDetails(order, items) {
            const paymentStatusClass = order.payment_status === 'Paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
            const orderStatusClass = order.order_status === 'Completed' ? 'bg-green-100 text-green-800' : 
                                     order.order_status === 'Preparing' ? 'bg-blue-100 text-blue-800' : 
                                     'bg-gray-100 text-gray-800';
            
            let itemsHtml = '';
            items.forEach(item => {
                const modifiers = item.modifiers_json ? JSON.parse(item.modifiers_json) : {};
                const modifierText = modifiers.size ? ` (${modifiers.size})` : '';
                
                itemsHtml += `
                    <tr class="border-b">
                        <td class="py-2">${item.product_name}${modifierText}</td>
                        <td class="py-2 text-center">${item.quantity}</td>
                        <td class="py-2 text-right">$${parseFloat(item.unit_price).toFixed(2)}</td>
                        <td class="py-2 text-right font-semibold">$${parseFloat(item.subtotal).toFixed(2)}</td>
                    </tr>
                `;
            });
            
            const notesHtml = order.notes ? `
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="font-semibold text-sm text-yellow-800 mb-2">
                        <i class="fas fa-sticky-note mr-2"></i>Payment Notes:
                    </p>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">${order.notes}</p>
                </div>
            ` : '';
            
            const html = `
                <div class="space-y-6">
                    <!-- Order Info -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Order Number</p>
                            <p class="font-semibold">${order.order_number}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date</p>
                            <p class="font-semibold">${new Date(order.created_at).toLocaleString()}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Customer</p>
                            <p class="font-semibold">${order.customer_name}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Payment Method</p>
                            <p class="font-semibold">${order.payment_method}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Payment Status</p>
                            <span class="inline-block px-3 py-1 rounded text-sm ${paymentStatusClass}">${order.payment_status}</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Order Status</p>
                            <span class="inline-block px-3 py-1 rounded text-sm ${orderStatusClass}">${order.order_status}</span>
                        </div>
                    </div>
                    
                    <!-- Items -->
                    <div>
                        <h4 class="font-semibold mb-3">Order Items</h4>
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-2 px-2 text-left text-sm">Item</th>
                                    <th class="py-2 px-2 text-center text-sm">Qty</th>
                                    <th class="py-2 px-2 text-right text-sm">Price</th>
                                    <th class="py-2 px-2 text-right text-sm">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHtml}
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Totals -->
                    <div class="border-t pt-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-semibold">$${parseFloat(order.subtotal).toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Tax:</span>
                            <span class="font-semibold">$${parseFloat(order.tax_amount).toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t pt-2">
                            <span>Total:</span>
                            <span class="text-orange-600">$${parseFloat(order.total_amount).toFixed(2)} ${order.currency}</span>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    ${notesHtml}
                </div>
            `;
            
            document.getElementById('orderDetailsContent').innerHTML = html;
        }
        
        function closeOrderDetails() {
            document.getElementById('orderDetailsModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('orderDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderDetails();
            }
        });
    </script>
</body>
</html>
