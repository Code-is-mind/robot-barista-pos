<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/languages.php';

$db = Database::getInstance()->getConnection();

// Handle language switch
if (isset($_GET['lang'])) {
    setLang($_GET['lang']);
    // Redirect to remove lang parameter from URL
    $redirect = 'index.php';
    if (isset($_GET['category'])) {
        $redirect .= '?category=' . $_GET['category'];
    }
    header('Location: ' . $redirect);
    exit;
}

// Get settings including UI customization
$sql = "SELECT * FROM settings";
$stmt = $db->query($sql);
$settingsData = $stmt->fetchAll();
$settings = [];
foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}
$exchangeRate = $settings['exchange_rate_usd_to_khr'] ?? 4100;

// UI Customization settings
$navbarColor = $settings['ui_navbar_color'] ?? '#16a34a';
$bgColor = $settings['ui_bg_color'] ?? '#f3f4f6';
$primaryColor = $settings['ui_primary_color'] ?? '#16a34a';
$businessName = $settings['business_name'] ?? 'Robot Barista';
$bgImage = $settings['ui_bg_image'] ?? '';

// Get categories
$sql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order, name";
$stmt = $db->query($sql);
$categories = $stmt->fetchAll();

// Get products (filter by category if provided)
$categoryId = $_GET['category'] ?? null;
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_available = 1";
if ($categoryId) {
    $sql .= " AND p.category_id = :category_id";
}
$sql .= " ORDER BY p.display_order, p.name";

$stmt = $db->prepare($sql);
if ($categoryId) {
    $stmt->execute([':category_id' => $categoryId]);
} else {
    $stmt->execute();
}
$products = $stmt->fetchAll();

// Get currency from session or default to USD
$currency = $_SESSION['currency'] ?? 'USD';
if (isset($_GET['currency'])) {
    $currency = $_GET['currency'];
    $_SESSION['currency'] = $currency;
}

$currentLang = getLang();
?>
<!DOCTYPE html>
<html lang="<?= $currentLang === 'kh' ? 'km' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($businessName) ?> - <?= t('self_service_kiosk') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --navbar-color: <?= $navbarColor ?>;
            --bg-color: <?= $bgColor ?>;
            --primary-color: <?= $primaryColor ?>;
        }
        body {
            background-color: var(--bg-color);
            <?php if (!empty($bgImage)): ?>
            background-image: url('../../public/uploads/<?= htmlspecialchars($bgImage) ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            <?php endif; ?>
        }
        .custom-navbar {
            background: linear-gradient(to right, var(--navbar-color), var(--navbar-color));
            filter: brightness(0.95);
        }
        .custom-primary {
            background-color: var(--primary-color);
        }
        .custom-primary-text {
            color: var(--primary-color);
        }
        .custom-primary-hover:hover {
            background-color: var(--primary-color);
            opacity: 0.9;
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
    
    <!-- Preload product page resources -->
    <link rel="preload" href="https://code.jquery.com/jquery-3.6.0.min.js" as="script">
    <link rel="preload" href="https://github.com/davidhuotkeo/bakong-khqr/releases/download/bakong-khqr-1.0.6/khqr-1.0.6.min.js" as="script">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js" as="script">
    
    <!-- Prefetch product page -->
    <link rel="prefetch" href="product.php">
</head>
<body>
    <!-- Header -->
    <header class="custom-navbar text-white shadow-lg">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-robot text-4xl"></i>
                <div>
                    <h1 class="text-2xl font-bold"><?= htmlspecialchars($businessName) ?></h1>
                    <p class="text-sm opacity-90"><?= t('self_service_kiosk') ?></p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <!-- Language Switcher -->
                <a href="?lang=<?= $currentLang === 'en' ? 'kh' : 'en' ?><?= $categoryId ? '&category=' . $categoryId : '' ?>" 
                   class="bg-white px-4 py-2 rounded-lg font-semibold hover:bg-opacity-90 transition custom-primary-text">
                    <i class="fas fa-language"></i> <?= $currentLang === 'en' ? 'ខ្មែរ' : 'EN' ?>
                </a>
                <!-- Currency Switcher -->
                <a href="?currency=<?= $currency === 'USD' ? 'KHR' : 'USD' ?><?= $categoryId ? '&category=' . $categoryId : '' ?>" 
                   class="bg-white px-4 py-2 rounded-lg font-semibold hover:bg-opacity-90 transition custom-primary-text">
                    <i class="fas fa-<?= $currency === 'USD' ? 'dollar-sign' : 'coins' ?>"></i> <?= $currency ?>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6">
        <!-- Categories -->
        <div class="flex space-x-3 mb-6 overflow-x-auto pb-2">
            <a href="index.php" class="px-6 py-3 rounded-lg font-semibold whitespace-nowrap <?= !$categoryId ? 'custom-primary text-white' : 'bg-white custom-primary-hover' ?>">
                <i class="fas fa-th"></i> <?= t('all') ?>
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="?category=<?= $cat['id'] ?>" 
               class="px-6 py-3 rounded-lg font-semibold whitespace-nowrap <?= $categoryId == $cat['id'] ? 'custom-primary text-white' : 'bg-white custom-primary-hover' ?>">
                <i class="fas <?= htmlspecialchars($cat['icon'] ?? 'fa-coffee') ?>"></i> <?= htmlspecialchars($cat['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($products as $product): 
                // Calculate price using current exchange rate
                $priceUSD = $product['price_usd'];
                $priceKHR = $priceUSD * $exchangeRate;
                $price = $currency === 'USD' ? $priceUSD : $priceKHR;
                $symbol = $currency === 'USD' ? '$' : '៛';
            ?>
            <a href="product.php?id=<?= $product['id'] ?>" class="product-card bg-white rounded-lg shadow-lg overflow-hidden cursor-pointer hover:shadow-xl transition-shadow">
                <img src="<?= $product['image'] ? '../../public/uploads/' . htmlspecialchars($product['image']) : 'https://via.placeholder.com/300x200?text=' . urlencode($product['name']) ?>" 
                     class="w-full h-40 object-cover" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="text-gray-600 text-sm mb-2 line-clamp-2"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                    <p class="font-bold text-xl custom-primary-text">
                        <?= $symbol ?><?= $currency === 'USD' ? number_format($price, 2) : number_format($price, 0) ?>
                    </p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
        <div class="text-center py-12">
            <i class="fas fa-coffee text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-xl"><?= t('no_products_available') ?></p>
        </div>
        <?php endif; ?>
    </main>
    
    <!-- Instant navigation feedback -->
    <script>
        // Add instant visual feedback when clicking product cards
        document.addEventListener('DOMContentLoaded', function() {
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Add loading state
                    this.style.opacity = '0.6';
                    this.style.transform = 'scale(0.98)';
                    
                    // Show loading indicator
                    const loader = document.createElement('div');
                    loader.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;';
                    loader.innerHTML = '<div style="border: 4px solid #f3f4f6; border-top: 4px solid #16a34a; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite;"></div>';
                    document.body.appendChild(loader);
                });
            });
        });
    </script>
</body>
</html>
