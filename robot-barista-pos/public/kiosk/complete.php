<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

$orderId = $_GET['order_id'] ?? null;
if (!$orderId) {
    header('Location: index.php');
    exit;
}

// Get order
$sql = "SELECT * FROM orders WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Handle receipt choice
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clear cart
    $_SESSION['cart'] = [];
    
    if (isset($_POST['print_receipt'])) {
        // Redirect to receipt page
        header('Location: ../../print-receipt.php?order_id=' . $orderId);
        exit;
    } else {
        // Skip receipt and go to success
        header('Location: success.php?order_number=' . urlencode($order['order_number']));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Complete - Robot Barista</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <main class="container mx-auto px-4 py-12 max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
            <h2 class="text-2xl font-bold mb-4">Payment Successful!</h2>
            <p class="text-gray-600 mb-6">Would you like to print your receipt?</p>
            
            <form method="POST">
                <button type="submit" name="print_receipt" class="w-full bg-orange-500 text-white py-3 rounded-lg font-semibold hover:bg-orange-600 mb-2 transition">
                    <i class="fas fa-print"></i> Yes, Print Receipt
                </button>
                <button type="submit" name="skip_receipt" class="w-full bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400 transition">
                    No, Thanks
                </button>
            </form>
        </div>
    </main>
</body>
</html>
