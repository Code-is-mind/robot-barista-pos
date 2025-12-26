<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - 80mm</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        @media print {
            body { 
                margin: 0;
                padding: 0;
            }
            .no-print { display: none; }
        }
        body {
            font-family: 'Courier New', monospace;
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            font-size: 12px;
            line-height: 1.4;
        }
        .receipt {
            width: 100%;
        }
        .center { 
            text-align: center; 
        }
        .bold { 
            font-weight: bold; 
        }
        .line { 
            border-top: 1px dashed #000; 
            margin: 8px 0; 
        }
        .row { 
            display: flex; 
            justify-content: space-between; 
            margin: 3px 0;
            font-size: 11px;
        }
        .item-row {
            margin: 5px 0;
        }
        .item-name {
            font-weight: bold;
            font-size: 12px;
        }
        .item-details {
            font-size: 10px;
            color: #333;
            padding-left: 10px;
        }
        .total { 
            font-size: 14px; 
            font-weight: bold; 
        }
        .header {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subheader {
            font-size: 11px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php
    require_once __DIR__ . '/config/database.php';
    
    $orderId = $_GET['order_id'] ?? null;
    
    if (!$orderId) {
        echo '<p>Order ID required</p>';
        exit;
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Get settings for business name
        $sql = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('business_name', 'business_address', 'business_phone')";
        $stmt = $db->query($sql);
        $settingsData = $stmt->fetchAll();
        $settings = [];
        foreach ($settingsData as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $businessName = $settings['business_name'] ?? 'Robot Barista';
        $businessAddress = $settings['business_address'] ?? '';
        $businessPhone = $settings['business_phone'] ?? '';
        
        // Get order
        $sql = "SELECT * FROM orders WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            echo '<p>Order not found</p>';
            exit;
        }
        
        // Get order items
        $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        $items = $stmt->fetchAll();
        
        $symbol = $order['currency'] === 'USD' ? '$' : '៛';
        
        ?>
        <div class="receipt">
            <div class="center header"><?= strtoupper(htmlspecialchars($businessName)) ?></div>
            <?php if ($businessAddress): ?>
            <div class="center subheader"><?= htmlspecialchars($businessAddress) ?></div>
            <?php endif; ?>
            <?php if ($businessPhone): ?>
            <div class="center subheader"><?= htmlspecialchars($businessPhone) ?></div>
            <?php endif; ?>
            <div class="line"></div>
            
            <div class="row">
                <span>Order #:</span>
                <span class="bold"><?= htmlspecialchars($order['order_number']) ?></span>
            </div>
            <div class="row">
                <span>Date:</span>
                <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="row">
                <span>Customer:</span>
                <span><?= htmlspecialchars($order['customer_name']) ?></span>
            </div>
            
            <div class="line"></div>
            
            <?php foreach ($items as $item): 
                $modifiers = json_decode($item['modifiers_json'], true);
            ?>
            <div class="item-row">
                <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                <div class="row item-details">
                    <span><?= $modifiers['size'] ?? 'Regular' ?> × <?= $item['quantity'] ?></span>
                    <span><?= $symbol ?><?= number_format($item['subtotal'], 2) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="line"></div>
            
            <div class="row">
                <span>Subtotal:</span>
                <span><?= $symbol ?><?= number_format($order['subtotal'], 2) ?></span>
            </div>
            <div class="row">
                <span>Tax (10%):</span>
                <span><?= $symbol ?><?= number_format($order['tax_amount'], 2) ?></span>
            </div>
            <div class="row total">
                <span>TOTAL:</span>
                <span><?= $symbol ?><?= number_format($order['total_amount'], 2) ?></span>
            </div>
            
            <div class="line"></div>
            
            <div class="row">
                <span>Payment:</span>
                <span class="bold"><?= htmlspecialchars($order['payment_method']) ?></span>
            </div>
            <div class="row">
                <span>Status:</span>
                <span class="bold"><?= htmlspecialchars($order['payment_status']) ?></span>
            </div>
            
            <div class="line"></div>
            
            <div class="center" style="margin-top: 10px; font-size: 11px;">
                Thank you for your order!<br>
                Enjoy your drink! ☕
            </div>
        </div>
        
        <div class="center no-print" style="margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; background: #10b981; color: white; border: none; border-radius: 5px;">
                <i class="fas fa-print"></i> Print Receipt (80mm)
            </button>
            <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; margin-left: 10px; background: #6b7280; color: white; border: none; border-radius: 5px;">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
        
        <script>
            // Auto-print on load (optional)
            // window.onload = function() { window.print(); }
        </script>
        
        <?php
    } catch (Exception $e) {
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    ?>
</body>
</html>
