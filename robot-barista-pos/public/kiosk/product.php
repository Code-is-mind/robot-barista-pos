<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/languages.php';
$db = Database::getInstance()->getConnection();

$currentLang = getLang();

$productId = $_GET['id'] ?? null;
if (!$productId) {
    header('Location: index.php');
    exit;
}

// Get product
$sql = "SELECT * FROM products WHERE id = :id AND is_available = 1";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

// Get currency early
$currency = $_SESSION['currency'] ?? 'USD';

// Optimize: Get settings and modifiers in parallel using a single query approach
// Get settings including UI customization
$sql = "SELECT * FROM settings WHERE setting_key IN ('exchange_rate_usd_to_khr', 'tax_percent', 'ui_navbar_color', 'ui_bg_color', 'ui_primary_color', 'ui_bg_image', 'business_name')";
$stmt = $db->query($sql);
$settingsData = $stmt->fetchAll();
$settings = [];
foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}
$exchangeRate = $settings['exchange_rate_usd_to_khr'] ?? 4100;
$taxPercent = $settings['tax_percent'] ?? 10;

// UI Customization settings
$navbarColor = $settings['ui_navbar_color'] ?? '#16a34a';
$bgColor = $settings['ui_bg_color'] ?? '#f3f4f6';
$primaryColor = $settings['ui_primary_color'] ?? '#16a34a';
$businessName = $settings['business_name'] ?? 'Robot Barista';
$bgImage = $settings['ui_bg_image'] ?? '';

// Get size modifiers only if product has modifiers
$sizeModifiers = [];
if ($product['has_modifiers']) {
    $sql = "SELECT * FROM modifiers WHERE type = 'size' AND is_active = 1 ORDER BY price_usd ASC";
    $stmt = $db->query($sql);
    $sizeModifiers = $stmt->fetchAll();
}

// Load payment configuration
require_once __DIR__ . '/../../config/payment.php';
$paymentConfig = getBakongConfig();

// Handle direct purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $size = $_POST['size'] ?? 'Small';
    $quantity = (int)($_POST['quantity'] ?? 1);
    $customerName = $_POST['customer_name'] ?: 'Walk-In Customer';
    
    // Calculate size price
    $sizePrice = 0;
    if ($size === 'Medium') {
        $sizePrice = 0.50;
    } elseif ($size === 'Large') {
        $sizePrice = 1.00;
    }
    
    $basePrice = $currency === 'USD' ? $product['price_usd'] : $product['price_khr'];
    $sizePriceConverted = $currency === 'USD' ? $sizePrice : $sizePrice * $exchangeRate;
    $unitPrice = $basePrice + $sizePriceConverted;
    $subtotal = $unitPrice * $quantity;
    $tax = $subtotal * ($taxPercent / 100);
    $total = $subtotal + $tax;
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Generate order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Insert order
        $sql = "INSERT INTO orders (
            order_number, customer_name, currency, subtotal, tax_amount, 
            total_amount, payment_method, payment_status, order_status
        ) VALUES (
            :order_number, :customer_name, :currency, :subtotal, :tax_amount,
            :total_amount, 'KHQR', 'Pending', 'Pending'
        )";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':order_number' => $orderNumber,
            ':customer_name' => $customerName,
            ':currency' => $currency,
            ':subtotal' => $subtotal,
            ':tax_amount' => $tax,
            ':total_amount' => $total
        ]);
        
        $orderId = $db->lastInsertId();
        
        // Insert order item
        $sql = "INSERT INTO order_items (
            order_id, product_id, product_name, quantity, 
            unit_price, modifiers_json, subtotal
        ) VALUES (
            :order_id, :product_id, :product_name, :quantity,
            :unit_price, :modifiers_json, :subtotal
        )";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':order_id' => $orderId,
            ':product_id' => $product['id'],
            ':product_name' => $product['name'],
            ':quantity' => $quantity,
            ':unit_price' => $unitPrice,
            ':modifiers_json' => json_encode(['size' => $size]),
            ':subtotal' => $subtotal
        ]);
        
        $db->commit();
        
        // Redirect to payment
        header('Location: payment.php?order_id=' . $orderId);
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error placing order: " . $e->getMessage();
    }
}

// Calculate price using current exchange rate
$priceUSD = $product['price_usd'];
$priceKHR = $priceUSD * $exchangeRate;
$price = $currency === 'USD' ? $priceUSD : $priceKHR;
$symbol = $currency === 'USD' ? '$' : '៛';
?>
<!DOCTYPE html>
<html lang="<?= $currentLang === 'kh' ? 'km' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - <?= htmlspecialchars($businessName) ?></title>
    
    <!-- Critical CSS inline for faster render -->
    <style>
        :root {
            --navbar-color: <?= $navbarColor ?>;
            --bg-color: <?= $bgColor ?>;
            --primary-color: <?= $primaryColor ?>;
        }
        body { 
            background-color: var(--bg-color); 
            margin: 0; 
            font-family: system-ui, -apple-system, sans-serif;
            <?php if (!empty($bgImage)): ?>
            background-image: url('../../public/uploads/<?= htmlspecialchars($bgImage) ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            <?php endif; ?>
        }
        .loading { display: flex; justify-content: center; align-items: center; height: 100vh; }
        .spinner { border: 4px solid #f3f4f6; border-top: 4px solid var(--primary-color); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .custom-navbar {
            background: linear-gradient(to right, var(--navbar-color), var(--navbar-color));
            filter: brightness(0.95);
        }
        .custom-primary {
            background-color: var(--primary-color) !important;
        }
        .custom-primary:hover {
            filter: brightness(0.9);
        }
        .custom-primary-text {
            color: var(--primary-color) !important;
        }
    </style>
    
    <!-- Khmer font -->
    <?php if ($currentLang === 'kh'): ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Battambang', sans-serif; }
    </style>
    <?php endif; ?>
    
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Load Font Awesome async -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    
    <!-- Load critical scripts synchronously (needed for inline scripts) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="khqr_lib/khqr-1.0.6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
</head>
<body>
    <header class="custom-navbar text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <a href="index.php" class="text-white hover:text-gray-200">
                <i class="fas fa-arrow-left"></i> <?= t('back_to_products') ?>
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-3 max-w-4xl">
        <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded mb-3 text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-lg p-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Left Column: Product Info & Options -->
                <div>
                    <div class="flex items-start gap-3 mb-3">
                        <img src="<?= $product['image'] ? '../../public/uploads/' . htmlspecialchars($product['image']) : 'https://via.placeholder.com/100x100?text=' . urlencode($product['name']) ?>" 
                             class="w-20 h-20 object-cover rounded-lg flex-shrink-0" alt="<?= htmlspecialchars($product['name']) ?>" loading="eager">
                        <div class="flex-1">
                            <h2 class="text-xl font-bold mb-1"><?= htmlspecialchars($product['name']) ?></h2>
                            <p class="text-gray-600 text-xs"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                        </div>
                    </div>
                    
                    <form method="POST" id="orderForm">
                        <!-- Customer Name -->
                        <div class="mb-3">
                            <label class="block font-semibold text-sm mb-1"><?= t('your_name_optional') ?>:</label>
                            <input type="text" name="customer_name" id="customerName" placeholder="<?= t('enter_your_name') ?>" 
                                   class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-orange-500 focus:outline-none">
                        </div>

                        <!-- Size Selection (only if product has modifiers) -->
                        <?php if ($product['has_modifiers']): ?>
                        <div class="mb-3">
                            <label class="block font-semibold text-sm mb-1"><?= t('size') ?>:</label>
                            <div class="flex space-x-2">
                                <?php foreach ($sizeModifiers as $index => $size): 
                                    // Calculate KHR price using current exchange rate
                                    $sizePriceUSD = $size['price_usd'];
                                    $sizePriceKHR = $sizePriceUSD * $exchangeRate;
                                    $sizePrice = $currency === 'USD' ? $sizePriceUSD : $sizePriceKHR;
                                    $sizeSymbol = $currency === 'USD' ? '$' : '៛';
                                ?>
                                <label class="flex-1">
                                    <input type="radio" name="size" value="<?= htmlspecialchars($size['name']) ?>" 
                                           data-price-usd="<?= $sizePriceUSD ?>" 
                                           data-price-khr="<?= $sizePriceKHR ?>"
                                           class="hidden peer" <?= $index === 0 ? 'checked' : '' ?>>
                                    <div class="border-2 border-gray-300 peer-checked:border-orange-500 peer-checked:bg-orange-50 py-2 rounded-lg text-center cursor-pointer">
                                        <p class="font-bold text-sm"><?= htmlspecialchars($size['name']) ?></p>
                                        <span class="text-xs text-gray-600">+<?= $sizeSymbol ?><?= $currency === 'USD' ? number_format($sizePrice, 2) : number_format($sizePrice, 0) ?></span>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Hidden input for products without modifiers -->
                        <input type="hidden" name="size" value="Standard">
                        <?php endif; ?>

                        <!-- Quantity -->
                        <div class="mb-3">
                            <label class="block font-semibold text-sm mb-1"><?= t('quantity') ?>:</label>
                            <div class="flex items-center justify-center space-x-3 bg-gray-50 rounded-lg p-3">
                                <button type="button" onclick="decreaseQty()" class="bg-red-500 hover:bg-red-600 text-white w-12 h-12 rounded-lg font-bold text-xl transition">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <div class="text-center">
                                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="20" readonly 
                                           class="w-16 text-3xl font-bold text-center bg-transparent border-none focus:outline-none">
                                    <p class="text-xs text-gray-600"><?= t('cups') ?></p>
                                </div>
                                <button type="button" onclick="increaseQty()" class="custom-primary text-white w-12 h-12 rounded-lg font-bold text-xl transition">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Price Summary -->
                <div>
                    <div class="bg-orange-50 border-2 border-orange-200 rounded-lg p-4 mb-3">
                        <h3 class="font-bold text-lg mb-3 text-orange-800"><?= t('order_summary') ?></h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-700"><?= t('base_price') ?>:</span>
                                <span class="font-semibold" id="basePrice"><?= $symbol ?><?= $currency === 'USD' ? number_format($price, 2) : number_format($price, 0) ?></span>
                            </div>
                            <?php if ($product['has_modifiers']): ?>
                            <div class="flex justify-between" id="sizeModifierRow">
                                <span class="text-gray-700"><?= t('size_modifier') ?>:</span>
                                <span class="font-semibold" id="sizeModifier"><?= $symbol ?>0.00</span>
                            </div>
                            <?php endif; ?>
                            <div class="flex justify-between">
                                <span class="text-gray-700"><?= t('subtotal') ?>:</span>
                                <span class="font-semibold" id="subtotal"><?= $symbol ?><?= $currency === 'USD' ? number_format($price, 2) : number_format($price, 0) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-700"><?= t('tax') ?> (<?= number_format($taxPercent, 0) ?>%):</span>
                                <span class="font-semibold" id="taxAmount"><?= $symbol ?><?= $currency === 'USD' ? number_format($price * ($taxPercent / 100), 2) : number_format($price * ($taxPercent / 100), 0) ?></span>
                            </div>
                            <div class="border-t-2 border-orange-300 pt-2 mt-2">
                                <div class="flex justify-between items-center">
                                    <p class="text-base font-bold text-gray-800"><?= t('total') ?>:</p>
                                    <p class="text-3xl font-bold text-orange-600" id="totalPrice">
                                        <?= $symbol ?><?= $currency === 'USD' ? number_format($price * (1 + $taxPercent / 100), 2) : number_format($price * (1 + $taxPercent / 100), 0) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Button -->
                    <button type="button" onclick="processOrder()" class="w-full custom-primary text-white py-4 rounded-lg text-xl font-semibold transition shadow-lg">
                        <i class="fas fa-check-circle"></i> <?= t('order_now') ?>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Payment Modal -->
    <div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg max-w-md w-full mx-4 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold"><?= t('scan_to_pay') ?></h2>
                <button onclick="cancelPayment()" class="text-gray-500 hover:text-red-600 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex justify-center mb-4">
                <canvas id="qrCodeCanvas" class="border-4 border-orange-500 rounded-lg"></canvas>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 mb-4">
                <div class="flex justify-between mb-2">
                    <span class="font-semibold"><?= t('amount') ?>:</span>
                    <span class="text-xl font-bold text-orange-600" id="paymentAmount"></span>
                </div>
                <div class="text-sm text-gray-600">
                    <p id="paymentDescription"></p>
                </div>
            </div>
            <div class="text-center mb-4">
                <div class="animate-pulse text-blue-600 mb-2">
                    <i class="fas fa-spinner fa-spin text-2xl"></i>
                    <p class="text-sm mt-2"><?= t('waiting_for_payment') ?></p>
                </div>
                <div class="mt-3 text-gray-600">
                    <i class="fas fa-clock"></i>
                    <span class="text-sm"><?= t('time_remaining') ?>: </span>
                    <span class="font-mono font-bold text-lg" id="paymentCountdown">2:00</span>
                </div>
                <p class="text-xs text-gray-500 mt-2"><?= t('payment_auto_cancel') ?></p>
            </div>
            <button onclick="cancelPayment()" class="w-full bg-red-500 text-white py-3 rounded-lg font-semibold hover:bg-red-600">
                <i class="fas fa-times-circle"></i> <?= t('cancel_payment') ?>
            </button>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receiptModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg max-w-md w-full mx-4 p-8 text-center">
            <i class="fas fa-check-circle text-6xl custom-primary-text mb-4"></i>
            <h2 class="text-2xl font-bold mb-4"><?= t('payment_successful') ?></h2>
            <p class="text-lg mb-6"><?= t('need_receipt') ?></p>
            <div class="flex space-x-4">
                <button onclick="printReceipt()" class="flex-1 bg-blue-500 text-white py-3 rounded-lg font-semibold hover:bg-blue-600">
                    <i class="fas fa-print"></i> <?= t('yes_print') ?>
                </button>
                <button onclick="skipReceipt()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400">
                    <?= t('no_thanks') ?>
                </button>
            </div>
            <p class="text-sm text-gray-500 mt-4"><?= t('auto_closing_in') ?> <span id="countdown">10</span> <?= t('seconds') ?>...</p>
        </div>
    </div>

    <!-- Preparing Modal -->
    <div id="preparingModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg max-w-md w-full mx-4 p-8 text-center">
            <i class="fas fa-robot text-8xl text-orange-600 mb-4 animate-bounce"></i>
            <h2 class="text-2xl font-bold mb-2"><?= t('preparing_order') ?></h2>
            <p class="text-gray-600 mb-4"><?= t('robot_making_drink') ?></p>
            <div class="flex justify-center space-x-2 mb-4">
                <div class="w-3 h-3 bg-orange-500 rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                <div class="w-3 h-3 bg-orange-500 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                <div class="w-3 h-3 bg-orange-500 rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
            </div>
            <p class="text-sm text-gray-500"><?= t('please_wait') ?> <span id="prepareCountdown">10</span> <?= t('seconds') ?>...</p>
        </div>
    </div>

    <script>
        // Price configuration
        const basePrice = <?= $price ?>;
        const exchangeRate = <?= $exchangeRate ?>;
        const currency = '<?= $currency ?>';
        const symbol = '<?= $symbol ?>';
        const taxPercent = <?= $taxPercent ?>;
        const isUSD = currency === 'USD';
        const productId = <?= $product['id'] ?>;
        const productName = '<?= addslashes($product['name']) ?>';
        
        // Translations for JavaScript
        const i18n = {
            paymentTimeout: '<?= addslashes(t('payment_timeout')) ?>',
            saleCompleted: '<?= addslashes(t('sale_completed')) ?>'
        };
        
        // Payment configuration from server
        const merchantConfig = {
            accountId: '<?= $paymentConfig['merchant']['account_id'] ?>',
            name: '<?= $paymentConfig['merchant']['name'] ?>',
            city: '<?= $paymentConfig['merchant']['city'] ?>',
            mobile: '<?= $paymentConfig['merchant']['mobile'] ?>'
        };

        // Build size prices from database (KHR calculated with current exchange rate)
        const hasModifiers = <?= $product['has_modifiers'] ? 'true' : 'false' ?>;
        const sizePrices = {};
        <?php if ($product['has_modifiers']): ?>
        <?php foreach ($sizeModifiers as $size): 
            $sizeUSD = $size['price_usd'];
            $sizeKHR = $sizeUSD * $exchangeRate;
        ?>
        sizePrices['<?= addslashes($size['name']) ?>'] = {
            usd: <?= $sizeUSD ?>,
            khr: <?= $sizeKHR ?>
        };
        <?php endforeach; ?>
        <?php else: ?>
        // No modifiers - standard size with no extra cost
        sizePrices['Standard'] = {
            usd: 0,
            khr: 0
        };
        <?php endif; ?>

        let currentOrderId = null;
        let currentTotal = 0;
        let paymentCheckInterval = null;
        let countdownInterval = null;
        let transactionResponse = null;

        function updatePrice() {
            let sizePrice = 0;
            
            if (hasModifiers) {
                const selectedSize = document.querySelector('input[name="size"]:checked').value;
                const sizeData = sizePrices[selectedSize];
                sizePrice = isUSD ? sizeData.usd : sizeData.khr;
            }
            
            const quantity = parseInt(document.getElementById('quantity').value);
            const unitPrice = basePrice + sizePrice;
            const subtotal = unitPrice * quantity;
            const tax = subtotal * (taxPercent / 100);
            const total = subtotal + tax;
            
            currentTotal = total;
            
            if (hasModifiers) {
                document.getElementById('sizeModifier').textContent = symbol + (isUSD ? sizePrice.toFixed(2) : Math.round(sizePrice));
            } else {
                document.getElementById('sizeModifierRow').style.display = 'none';
            }
            
            document.getElementById('subtotal').textContent = symbol + (isUSD ? subtotal.toFixed(2) : Math.round(subtotal));
            document.getElementById('taxAmount').textContent = symbol + (isUSD ? tax.toFixed(2) : Math.round(tax));
            document.getElementById('totalPrice').textContent = symbol + (isUSD ? total.toFixed(2) : Math.round(total));
        }

        function decreaseQty() {
            const qtyInput = document.getElementById('quantity');
            let qty = parseInt(qtyInput.value);
            if (qty > 1) {
                qtyInput.value = qty - 1;
                updatePrice();
            }
        }

        function increaseQty() {
            const qtyInput = document.getElementById('quantity');
            let qty = parseInt(qtyInput.value);
            if (qty < 20) {
                qtyInput.value = qty + 1;
                updatePrice();
            }
        }

        function processOrder() {
            const customerName = document.getElementById('customerName').value || 'Walk-In Customer';
            const selectedSize = hasModifiers ? document.querySelector('input[name="size"]:checked').value : 'Standard';
            const quantity = parseInt(document.getElementById('quantity').value);
            
            // Use the selected currency from kiosk
            let displayAmount, displayCurrency, displaySymbol;
            
            if (currency === 'USD') {
                displayAmount = currentTotal.toFixed(2);
                displayCurrency = 'USD';
                displaySymbol = '$';
            } else {
                displayAmount = Math.round(currentTotal);
                displayCurrency = 'KHR';
                displaySymbol = '៛';
            }
            
            const description = `${productName} (${selectedSize}) x${quantity} - Kiosk Order`;
            
            // Show payment modal and generate KHQR
            document.getElementById('paymentModal').classList.remove('hidden');
            document.getElementById('paymentAmount').textContent = displaySymbol + displayAmount.toLocaleString();
            document.getElementById('paymentDescription').textContent = description;
            
            generateKHQR(displayAmount, displayCurrency, description, customerName, selectedSize, quantity);
        }

        function cancelPayment() {
            // Stop payment checking
            if (paymentCheckInterval) {
                clearInterval(paymentCheckInterval);
                paymentCheckInterval = null;
            }
            
            // Stop countdown
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
            
            // Cancel order in database
            if (currentOrderId) {
                $.post('cancel_order.php', { order_id: currentOrderId });
            }
            
            // Hide modal
            document.getElementById('paymentModal').classList.add('hidden');
            
            // Reset
            currentOrderId = null;
        }

        function generateKHQR(amount, selectedCurrency, description, customerName, size, quantity) {
            const KHQR = typeof BakongKHQR !== "undefined" ? BakongKHQR : null;
            if (!KHQR) {
                alert('KHQR library not loaded');
                return;
            }

            const data = KHQR.khqrData;
            const Info = KHQR.IndividualInfo;
            
            // Set currency type based on selection
            const currencyType = selectedCurrency === 'USD' ? data.currency.usd : data.currency.khr;
            
            const optionalData = {
                currency: currencyType,
                amount: parseFloat(amount),
                mobileNumber: merchantConfig.mobile,
                storeLabel: "Robot Barista",
                terminalLabel: "Kiosk1",
                languagePreference: "km",
            };

            const individualInfo = new Info(
                merchantConfig.accountId,
                merchantConfig.name,
                merchantConfig.city,
                optionalData
            );

            const khqrInstance = new KHQR.BakongKHQR();
            const individual = khqrInstance.generateIndividual(individualInfo);
            
            const qrCode = document.getElementById('qrCodeCanvas');
            QRCode.toCanvas(qrCode, individual.data.qr, { width: 300 });
            
            // Create order in database
            createOrder(customerName, size, quantity, individual.data.md5);
        }

        function createOrder(customerName, size, quantity, md5Hash) {
            let sizePrice = 0;
            
            if (hasModifiers) {
                const sizeData = sizePrices[size];
                sizePrice = isUSD ? sizeData.usd : sizeData.khr;
            }
            
            const unitPrice = basePrice + sizePrice;
            const subtotal = unitPrice * quantity;
            const tax = subtotal * (taxPercent / 100);
            const total = subtotal + tax;

            console.log('Creating order:', {
                product_id: productId,
                product_name: productName,
                customer_name: customerName,
                size: size,
                quantity: quantity,
                unit_price: unitPrice,
                subtotal: subtotal,
                tax: tax,
                total: total,
                currency: currency,
                md5_hash: md5Hash
            });

            $.ajax({
                url: 'create_order.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    product_id: productId,
                    product_name: productName,
                    customer_name: customerName,
                    size: size,
                    quantity: quantity,
                    unit_price: unitPrice,
                    subtotal: subtotal,
                    tax: tax,
                    total: total,
                    currency: currency,
                    md5_hash: md5Hash
                }),
                success: function(response) {
                    console.log('Order created successfully:', response);
                    if (response.success) {
                        currentOrderId = response.order_id;
                        checkPaymentStatus(md5Hash);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Order creation failed:', error, xhr.responseText);
                }
            });
        }

        function checkPaymentStatus(md5Hash) {
            let checkCount = 0;
            const maxChecks = 120; // 120 seconds (2 minutes)
            const startTime = Date.now();
            const maxTime = 120 * 1000; // 120 seconds in milliseconds
            
            console.log('=== Starting Payment Check ===');
            console.log('MD5 Hash:', md5Hash);
            console.log('Order ID:', currentOrderId);
            console.log('Max checks:', maxChecks);
            
            // Update countdown display
            function updateCountdown() {
                const elapsed = Date.now() - startTime;
                const remaining = Math.max(0, maxTime - elapsed);
                const seconds = Math.ceil(remaining / 1000);
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                
                const countdownEl = document.getElementById('paymentCountdown');
                if (countdownEl) {
                    countdownEl.textContent = `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
                    console.log('Countdown updated:', seconds, 'seconds remaining');
                } else {
                    console.error('Countdown element not found!');
                }
                
                if (remaining <= 0) {
                    console.log('Payment timeout reached!');
                    clearInterval(countdownInterval);
                    clearInterval(paymentCheckInterval);
                    countdownInterval = null;
                    paymentCheckInterval = null;
                    onPaymentTimeout();
                }
            }
            
            // Update countdown every second
            console.log('Starting payment countdown: 120 seconds (2 minutes)');
            countdownInterval = setInterval(updateCountdown, 1000);
            updateCountdown(); // Initial update
            
            paymentCheckInterval = setInterval(function() {
                checkCount++;
                console.log(`\n--- Payment Check #${checkCount}/${maxChecks} ---`);
                
                // Check if timeout reached
                if (checkCount >= maxChecks) {
                    console.log('Max checks reached, timing out...');
                    clearInterval(paymentCheckInterval);
                    clearInterval(countdownInterval);
                    paymentCheckInterval = null;
                    onPaymentTimeout();
                    return;
                }
                
                console.log('Checking transaction with MD5:', md5Hash);
                
                $.ajax({
                    url: '../../payment/check_transaction.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ md5: md5Hash }),
                    success: function(response) {
                        console.log('✓ Transaction check response:', response);
                        console.log('  - Response Code:', response.responseCode);
                        console.log('  - Response Message:', response.responseMessage);
                        
                        if (response.data) {
                            console.log('  - Transaction Data:', response.data);
                        }
                        
                        if (response.responseCode === 0) {
                            console.log('✓✓✓ PAYMENT SUCCESSFUL! ✓✓✓');
                            clearInterval(paymentCheckInterval);
                            clearInterval(countdownInterval);
                            paymentCheckInterval = null;
                            transactionResponse = response;
                            onPaymentSuccess(response);
                        } else {
                            console.log('  - Payment not yet received, continuing to check...');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('✗ Payment check error:', {
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status
                        });
                    }
                });
            }, 1000);
        }
        
        function onPaymentTimeout() {
            // Cancel order
            if (currentOrderId) {
                $.post('cancel_order.php', { order_id: currentOrderId });
            }
            
            // Hide payment modal
            document.getElementById('paymentModal').classList.add('hidden');
            
            // Show timeout message
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50';
            toast.innerHTML = '<i class="fas fa-times-circle mr-2"></i> ' + i18n.paymentTimeout;
            document.body.appendChild(toast);
            
            // Redirect to kiosk after 3 seconds
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 3000);
        }

        function onPaymentSuccess(transactionData) {
            console.log('Processing payment success with transaction data:', transactionData);
            
            // Update order status with transaction data
            $.post('update_order_status.php', { 
                order_id: currentOrderId, 
                status: 'Paid',
                transaction_data: JSON.stringify(transactionData)
            }, function(response) {
                console.log('Order status updated:', response);
            });
            
            // Hide payment modal
            document.getElementById('paymentModal').classList.add('hidden');
            
            // Show receipt modal
            document.getElementById('receiptModal').classList.remove('hidden');
            
            // Start countdown
            let countdown = 10;
            const countdownInterval = setInterval(function() {
                countdown--;
                document.getElementById('countdown').textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    skipReceipt();
                }
            }, 1000);
        }

        function printReceipt() {
            console.log('Printing receipt for order:', currentOrderId);
            
            // Create hidden iframe for silent printing
            const printFrame = document.createElement('iframe');
            printFrame.style.display = 'none';
            printFrame.style.position = 'absolute';
            printFrame.style.width = '0';
            printFrame.style.height = '0';
            printFrame.style.border = 'none';
            
            // Set the source to the print receipt page
            printFrame.src = '../../print-receipt.php?order_id=' + currentOrderId;
            
            // Add to document
            document.body.appendChild(printFrame);
            
            // Wait for iframe to load, then trigger print
            printFrame.onload = function() {
                try {
                    console.log('Receipt loaded, triggering print...');
                    
                    // Trigger print on the iframe content
                    setTimeout(function() {
                        try {
                            printFrame.contentWindow.print();
                            console.log('Print dialog triggered successfully');
                        } catch (e) {
                            console.error('Print trigger error:', e);
                        }
                        
                        // Remove iframe after printing (or after 2 seconds)
                        setTimeout(function() {
                            document.body.removeChild(printFrame);
                            console.log('Print iframe removed');
                        }, 2000);
                    }, 500);
                } catch (e) {
                    console.error('Print error:', e);
                    document.body.removeChild(printFrame);
                }
            };
            
            printFrame.onerror = function() {
                console.error('Failed to load receipt page');
                document.body.removeChild(printFrame);
            };
            
            // Continue to next step immediately (don't wait for print)
            skipReceipt();
        }

        function skipReceipt() {
            document.getElementById('receiptModal').classList.add('hidden');
            showPreparingModal();
        }

        function showPreparingModal() {
            document.getElementById('preparingModal').classList.remove('hidden');
            
            let countdown = 10;
            const countdownInterval = setInterval(function() {
                countdown--;
                document.getElementById('prepareCountdown').textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    showSuccessAndRedirect();
                }
            }, 1000);
        }

        function showSuccessAndRedirect() {
            document.getElementById('preparingModal').classList.add('hidden');
            
            // Show success toast
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 custom-primary text-white px-6 py-4 rounded-lg shadow-lg z-50';
            toast.innerHTML = '<i class="fas fa-check-circle mr-2"></i> ' + i18n.saleCompleted;
            document.body.appendChild(toast);
            
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 2000);
        }

        if (hasModifiers) {
            document.querySelectorAll('input[name="size"]').forEach(radio => {
                radio.addEventListener('change', updatePrice);
            });
        }

        updatePrice();
    </script>
</body>
</html>
