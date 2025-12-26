<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get settings from database
    $sql = "SELECT setting_key, setting_value FROM settings 
            WHERE setting_key IN ('business_name', 'ui_navbar_color', 'ui_bg_color', 'ui_primary_color', 'ui_bg_image')";
    $stmt = $db->query($sql);
    $settingsData = $stmt->fetchAll();
    
    $settings = [];
    foreach ($settingsData as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
    
    // Set defaults
    $businessName = $settings['business_name'] ?? 'Robot Barista';
    $navbarColor = $settings['ui_navbar_color'] ?? '#f97316';
    $bgColor = $settings['ui_bg_color'] ?? '#f97316';
    $primaryColor = $settings['ui_primary_color'] ?? '#16a34a';
    $bgImage = $settings['ui_bg_image'] ?? '';
    
    // Get product count
    $sql = "SELECT COUNT(*) as count FROM products WHERE is_available = 1";
    $stmt = $db->query($sql);
    $productCount = $stmt->fetch()['count'] ?? 0;
    
    // Get category count
    $sql = "SELECT COUNT(*) as count FROM categories WHERE is_active = 1";
    $stmt = $db->query($sql);
    $categoryCount = $stmt->fetch()['count'] ?? 0;
    
} catch (Exception $e) {
    // Fallback to defaults if database fails
    $businessName = 'Robot Barista';
    $navbarColor = '#f97316';
    $bgColor = '#f97316';
    $primaryColor = '#16a34a';
    $bgImage = '';
    $productCount = 0;
    $categoryCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($businessName) ?> - Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        :root {
            --navbar-color: <?= $navbarColor ?>;
            --bg-color: <?= $bgColor ?>;
            --primary-color: <?= $primaryColor ?>;
        }
        
        body {
            <?php if (!empty($bgImage)): ?>
            background-image: url('uploads/<?= htmlspecialchars($bgImage) ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            <?php else: ?>
            background: linear-gradient(to bottom right, var(--bg-color), <?= $navbarColor ?>);
            <?php endif; ?>
        }
        
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            filter: brightness(0.9);
        }
        
        .btn-secondary {
            background-color: var(--navbar-color);
        }
        
        .btn-secondary:hover {
            filter: brightness(0.9);
        }
        
        .badge-primary {
            background-color: rgba(22, 163, 74, 0.1);
            color: var(--primary-color);
        }
        
        .badge-secondary {
            background-color: rgba(249, 115, 22, 0.1);
            color: var(--navbar-color);
        }
        
        .feature-box-primary {
            background-color: rgba(22, 163, 74, 0.05);
            border-left: 3px solid var(--primary-color);
        }
        
        .feature-box-secondary {
            background-color: rgba(249, 115, 22, 0.05);
            border-left: 3px solid var(--navbar-color);
        }
        
        .icon-primary {
            color: var(--primary-color);
        }
        
        .icon-secondary {
            color: var(--navbar-color);
        }
        
        .text-primary {
            color: var(--primary-color);
        }
        
        .text-secondary {
            color: var(--navbar-color);
        }
        
        .overlay {
            <?php if (!empty($bgImage)): ?>
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            <?php endif; ?>
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="overlay min-h-screen">
        <div class="container mx-auto px-4 py-12">
            <div class="text-center mb-12">
                <i class="fas fa-robot text-8xl text-white mb-4 drop-shadow-lg"></i>
                <h1 class="text-5xl font-bold text-white mb-4 drop-shadow-lg">
                    <?= htmlspecialchars($businessName) ?>
                </h1>
                <p class="text-xl text-white opacity-90 drop-shadow">
                    Self-Service Point of Sale System
                </p>
                <?php if ($productCount > 0 || $categoryCount > 0): ?>
                <div class="mt-4 flex justify-center gap-4">
                    <?php if ($productCount > 0): ?>
                    <span class="bg-white bg-opacity-20 text-white px-4 py-2 rounded-full text-sm backdrop-blur">
                        <i class="fas fa-coffee"></i> <?= $productCount ?> Products
                    </span>
                    <?php endif; ?>
                    <?php if ($categoryCount > 0): ?>
                    <span class="bg-white bg-opacity-20 text-white px-4 py-2 rounded-full text-sm backdrop-blur">
                        <i class="fas fa-th"></i> <?= $categoryCount ?> Categories
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto mb-8">
                <!-- Customer Kiosk -->
                <div class="card rounded-lg shadow-2xl p-8">
                    <div class="text-center mb-6">
                        <i class="fas fa-shopping-cart text-6xl icon-primary mb-4"></i>
                        <h2 class="text-2xl font-bold mb-2">Customer Kiosk</h2>
                        <span class="badge-primary text-xs font-semibold px-3 py-1 rounded-full">
                            Self-Service
                        </span>
                    </div>
                    <p class="text-gray-600 mb-6 text-center">
                        Browse products, add to cart, and checkout with KHQR payment.
                    </p>
                    <a href="kiosk/index.php" 
                       class="block w-full btn-primary text-white py-4 rounded-lg font-semibold text-center transition text-lg">
                        <i class="fas fa-coffee"></i> Start Ordering
                    </a>
                    <div class="mt-4 p-4 feature-box-primary rounded-lg text-sm">
                        <p class="font-semibold text-primary mb-2">
                            <i class="fas fa-check-circle"></i> Features:
                        </p>
                        <ul class="text-gray-700 text-sm space-y-1">
                            <li><i class="fas fa-angle-right text-primary"></i> Browse products by category</li>
                            <li><i class="fas fa-angle-right text-primary"></i> Real-time shopping cart</li>
                            <li><i class="fas fa-angle-right text-primary"></i> Currency toggle (USD/KHR)</li>
                            <li><i class="fas fa-angle-right text-primary"></i> KHQR payment integration</li>
                            <li><i class="fas fa-angle-right text-primary"></i> Receipt printing</li>
                        </ul>
                    </div>
                </div>

                <!-- Admin Panel -->
                <div class="card rounded-lg shadow-2xl p-8">
                    <div class="text-center mb-6">
                        <i class="fas fa-lock text-6xl icon-secondary mb-4"></i>
                        <h2 class="text-2xl font-bold mb-2">Admin Panel</h2>
                        <span class="badge-secondary text-xs font-semibold px-3 py-1 rounded-full">
                            Management
                        </span>
                    </div>
                    <p class="text-gray-600 mb-6 text-center">
                        Manage products, view orders, and generate sales reports.
                    </p>
                    <a href="admin/login.html" 
                       class="block w-full btn-secondary text-white py-4 rounded-lg font-semibold text-center transition text-lg">
                        <i class="fas fa-sign-in-alt"></i> Admin Login
                    </a>
                    <div class="mt-4 p-4 feature-box-secondary rounded-lg text-sm">
                        <p class="font-semibold text-secondary mb-2">
                            <i class="fas fa-cog"></i> Features:
                        </p>
                        <ul class="text-gray-700 text-sm space-y-1">
                            <li><i class="fas fa-angle-right text-secondary"></i> Dashboard with statistics</li>
                            <li><i class="fas fa-angle-right text-secondary"></i> Product & category management</li>
                            <li><i class="fas fa-angle-right text-secondary"></i> Order tracking & status</li>
                            <li><i class="fas fa-angle-right text-secondary"></i> Sales reports & analytics</li>
                            <li><i class="fas fa-angle-right text-secondary"></i> UI customization settings</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <?php if ($productCount > 0 || $categoryCount > 0): ?>
            <div class="max-w-4xl mx-auto mb-8">
                <div class="card rounded-lg shadow-xl p-6">
                    <h3 class="text-xl font-bold text-center mb-4">
                        <i class="fas fa-chart-line text-primary"></i> System Overview
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-coffee text-3xl text-primary mb-2"></i>
                            <p class="text-2xl font-bold text-gray-800"><?= $productCount ?></p>
                            <p class="text-sm text-gray-600">Products</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-th text-3xl text-secondary mb-2"></i>
                            <p class="text-2xl font-bold text-gray-800"><?= $categoryCount ?></p>
                            <p class="text-sm text-gray-600">Categories</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-qrcode text-3xl text-primary mb-2"></i>
                            <p class="text-2xl font-bold text-gray-800">KHQR</p>
                            <p class="text-sm text-gray-600">Payment</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-print text-3xl text-secondary mb-2"></i>
                            <p class="text-2xl font-bold text-gray-800">80mm</p>
                            <p class="text-sm text-gray-600">Thermal</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="text-center mt-12">
                <div class="card inline-block px-8 py-4 rounded-lg shadow-lg">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-code"></i> <?= htmlspecialchars($businessName) ?> POS v1.0
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Built with PHP, MySQL & Tailwind CSS
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
