<?php
$orderNumber = $_GET['order_number'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Ready - Robot Barista</title>
    <meta http-equiv="refresh" content="5;url=index.php">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <main class="container mx-auto px-4 py-12 max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <i class="fas fa-robot text-8xl text-orange-600 mb-4"></i>
            <h2 class="text-2xl font-bold mb-2">Preparing Your Order</h2>
            <p class="text-gray-600 mb-4">Please wait while our robot barista prepares your drinks</p>
            <p class="text-lg font-semibold mb-6">Order #<?= htmlspecialchars($orderNumber) ?></p>
            
            <div class="text-4xl mb-6">
                <span class="animate-pulse">.</span>
                <span class="animate-pulse">.</span>
                <span class="animate-pulse">.</span>
            </div>
            
            <p class="text-sm text-gray-500 mb-4">Redirecting in 5 seconds...</p>
            
            <a href="index.php" class="inline-block bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700">
                Start New Order
            </a>
        </div>
    </main>
</body>
</html>
