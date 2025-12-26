<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key !== 'action') {
            $sql = "UPDATE settings SET setting_value = :value WHERE setting_key = :key";
            $stmt = $db->prepare($sql);
            $stmt->execute([':value' => $value, ':key' => $key]);
        }
    }
    $success = "Settings updated successfully!";
}

// Get all settings
$sql = "SELECT * FROM settings ORDER BY setting_key";
$stmt = $db->query($sql);
$settingsData = $stmt->fetchAll();

// Convert to associative array
$settings = [];
foreach ($settingsData as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Robot Barista Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto lg:ml-0">
            <header class="bg-white shadow-sm p-4 pl-16 lg:pl-4">
                <h2 class="text-lg lg:text-2xl font-bold">Settings</h2>
            </header>

            <div class="p-6">
                <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- General Settings -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">
                            <i class="fas fa-cog text-blue-600"></i> General Settings
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold mb-2">Exchange Rate (USD to KHR)</label>
                                <input type="number" step="1" name="exchange_rate_usd_to_khr" 
                                       value="<?= htmlspecialchars($settings['exchange_rate_usd_to_khr'] ?? '4100') ?>"
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block font-semibold mb-2">Tax Percentage (%)</label>
                                <input type="number" step="0.01" name="tax_percent" 
                                       value="<?= htmlspecialchars($settings['tax_percent'] ?? '10') ?>"
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            </div>
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">
                            <i class="fas fa-building text-green-600"></i> Business Information
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block font-semibold mb-2">Business Name</label>
                                <input type="text" name="business_name" 
                                       value="<?= htmlspecialchars($settings['business_name'] ?? '') ?>"
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                                <p class="text-xs text-gray-500 mt-1">Appears on kiosk, receipts, and bills</p>
                            </div>
                            <div>
                                <label class="block font-semibold mb-2">Business Address</label>
                                <input type="text" name="business_address" 
                                       value="<?= htmlspecialchars($settings['business_address'] ?? '') ?>"
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block font-semibold mb-2">Business Phone</label>
                                <input type="text" name="business_phone" 
                                       value="<?= htmlspecialchars($settings['business_phone'] ?? '') ?>"
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            </div>
                        </div>
                    </div>

                    <!-- UI Customization -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">
                            <i class="fas fa-palette text-pink-600"></i> Kiosk UI Customization
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold mb-2">Navbar Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" name="ui_navbar_color" 
                                           value="<?= htmlspecialchars($settings['ui_navbar_color'] ?? '#16a34a') ?>"
                                           class="w-16 h-10 border-2 border-gray-300 rounded cursor-pointer">
                                    <input type="text" name="ui_navbar_color_text" 
                                           value="<?= htmlspecialchars($settings['ui_navbar_color'] ?? '#16a34a') ?>"
                                           class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2"
                                           readonly>
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold mb-2">Background Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" name="ui_bg_color" 
                                           value="<?= htmlspecialchars($settings['ui_bg_color'] ?? '#f3f4f6') ?>"
                                           class="w-16 h-10 border-2 border-gray-300 rounded cursor-pointer">
                                    <input type="text" name="ui_bg_color_text" 
                                           value="<?= htmlspecialchars($settings['ui_bg_color'] ?? '#f3f4f6') ?>"
                                           class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2"
                                           readonly>
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold mb-2">Primary/Accent Color</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" name="ui_primary_color" 
                                           value="<?= htmlspecialchars($settings['ui_primary_color'] ?? '#16a34a') ?>"
                                           class="w-16 h-10 border-2 border-gray-300 rounded cursor-pointer">
                                    <input type="text" name="ui_primary_color_text" 
                                           value="<?= htmlspecialchars($settings['ui_primary_color'] ?? '#16a34a') ?>"
                                           class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2"
                                           readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Background Image Upload -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label class="block font-semibold mb-2">
                                <i class="fas fa-image"></i> Background Image (Optional)
                            </label>
                            <p class="text-xs text-gray-500 mb-3">Upload an image to use as kiosk background. Leave empty to use solid color.</p>
                            
                            <div id="bgImagePreview" class="mb-3 <?= empty($settings['ui_bg_image'] ?? '') ? 'hidden' : '' ?>">
                                <div class="relative inline-block">
                                    <img src="../uploads/<?= htmlspecialchars($settings['ui_bg_image'] ?? '') ?>" 
                                         alt="Background Preview" 
                                         class="h-32 rounded-lg border-2 border-gray-300"
                                         id="bgImagePreviewImg">
                                    <button type="button" onclick="removeBgImage()" 
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <input type="file" id="bgImageInput" accept="image/*" class="hidden">
                                <button type="button" onclick="document.getElementById('bgImageInput').click()" 
                                        class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700">
                                    <i class="fas fa-upload"></i> Upload Image
                                </button>
                                <span id="bgImageStatus" class="text-sm text-gray-600"></span>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Recommended: 1920x1080px or larger. Max 5MB. JPG, PNG, GIF, WebP</p>
                        </div>
                        
                        <div class="mt-4 p-4 bg-pink-50 rounded-lg">
                            <p class="text-sm text-pink-800">
                                <i class="fas fa-info-circle"></i> 
                                These colors will be applied to the customer-facing kiosk interface
                            </p>
                        </div>
                    </div>

                    <!-- KHQR Payment Settings -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">
                            <i class="fas fa-qrcode text-orange-600"></i> KHQR Payment Settings
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block font-semibold mb-2">KHQR Merchant ID</label>
                                <input type="text" name="khqr_merchant_id" 
                                       value="<?= htmlspecialchars($settings['khqr_merchant_id'] ?? '') ?>"
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block font-semibold mb-2">Bank Account Number</label>
                                <input type="text" name="khqr_bank_account" 
                                       value="<?= htmlspecialchars($settings['khqr_bank_account'] ?? '') ?>"
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block font-semibold mb-2">Merchant Name</label>
                                <input type="text" name="khqr_merchant_name" 
                                       value="<?= htmlspecialchars($settings['khqr_merchant_name'] ?? '') ?>"
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            </div>
                        </div>
                    </div>

                    <!-- Printer Settings -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">
                            <i class="fas fa-print text-purple-600"></i> Printer Settings (80mm Thermal)
                        </h3>
                        
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="printer_enabled" value="1" 
                                       <?= ($settings['printer_enabled'] ?? '1') == '1' ? 'checked' : '' ?>
                                       class="mr-2 w-5 h-5">
                                <span class="font-semibold">Enable Auto-Print</span>
                            </label>
                            <p class="text-xs text-gray-500 ml-7">When enabled, receipts will automatically print to the configured printer</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block font-semibold mb-2">Printer IP Address</label>
                                <input type="text" name="printer_ip" 
                                       value="<?= htmlspecialchars($settings['printer_ip'] ?? '192.168.1.100') ?>"
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2"
                                       placeholder="192.168.1.100">
                            </div>
                            <div>
                                <label class="block font-semibold mb-2">Printer Port</label>
                                <input type="text" name="printer_port" 
                                       value="<?= htmlspecialchars($settings['printer_port'] ?? '9100') ?>"
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2"
                                       placeholder="9100">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold mb-2">Printer Type</label>
                                <select name="printer_type" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                                    <option value="network" <?= ($settings['printer_type'] ?? 'network') == 'network' ? 'selected' : '' ?>>Network Printer (ESC/POS)</option>
                                    <option value="usb" <?= ($settings['printer_type'] ?? 'network') == 'usb' ? 'selected' : '' ?>>USB Printer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold mb-2">Paper Width</label>
                                <select name="printer_paper_width" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                                    <option value="80" <?= ($settings['printer_paper_width'] ?? '80') == '80' ? 'selected' : '' ?>>80mm (Standard)</option>
                                    <option value="58" <?= ($settings['printer_paper_width'] ?? '80') == '58' ? 'selected' : '' ?>>58mm (Compact)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-4 bg-purple-50 rounded-lg">
                            <p class="text-sm text-purple-800 mb-2">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Setup Instructions:</strong>
                            </p>
                            <ul class="text-xs text-purple-700 list-disc list-inside space-y-1">
                                <li>Connect your thermal printer to the network</li>
                                <li>Find the printer's IP address (usually printed on test page)</li>
                                <li>Default port for ESC/POS printers is 9100</li>
                                <li>Test the connection after saving settings</li>
                                <li>If auto-print fails, receipt will open in browser as fallback</li>
                            </ul>
                            <div class="mt-3">
                                <a href="test_printer.php" class="inline-block bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700">
                                    <i class="fas fa-print"></i> Test Printer Connection
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700">
                            <i class="fas fa-save"></i> Save All Settings
                        </button>
                    </div>
                </form>

                <!-- Change Password Section -->
                <div class="bg-white rounded-lg shadow p-6 mt-6">
                    <h3 class="text-xl font-bold mb-4">
                        <i class="fas fa-key text-red-600"></i> Change Password
                    </h3>
                    <form id="passwordForm">
                        <div class="space-y-4">
                            <div>
                                <label class="block font-semibold mb-2">Current Password</label>
                                <input type="password" id="current_password" required
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            </div>
                            <div>
                                <label class="block font-semibold mb-2">New Password</label>
                                <input type="password" id="new_password" required minlength="6"
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                                <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                            </div>
                            <div>
                                <label class="block font-semibold mb-2">Confirm New Password</label>
                                <input type="password" id="confirm_password" required
                                       class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            </div>
                        </div>
                        <div id="passwordMessage" class="hidden mt-4 px-4 py-3 rounded"></div>
                        <div class="mt-4">
                            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-red-700">
                                <i class="fas fa-lock"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        const BASE_URL = window.location.pathname.substring(0, window.location.pathname.indexOf('/public'));

        // Color picker sync
        document.querySelectorAll('input[type="color"]').forEach(colorInput => {
            const textInput = colorInput.nextElementSibling.nextElementSibling;
            
            colorInput.addEventListener('input', (e) => {
                textInput.value = e.target.value;
            });
        });

        // Background image upload
        document.getElementById('bgImageInput').addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;
            
            const statusEl = document.getElementById('bgImageStatus');
            statusEl.textContent = 'Uploading...';
            statusEl.className = 'text-sm text-blue-600';
            
            const formData = new FormData();
            formData.append('bg_image', file);
            
            try {
                const response = await fetch('upload-bg-image.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    statusEl.textContent = 'Uploaded successfully!';
                    statusEl.className = 'text-sm text-green-600';
                    
                    // Show preview
                    document.getElementById('bgImagePreviewImg').src = data.url + '?' + Date.now();
                    document.getElementById('bgImagePreview').classList.remove('hidden');
                    
                    setTimeout(() => {
                        statusEl.textContent = '';
                    }, 3000);
                } else {
                    statusEl.textContent = 'Error: ' + data.error;
                    statusEl.className = 'text-sm text-red-600';
                }
            } catch (error) {
                statusEl.textContent = 'Upload failed';
                statusEl.className = 'text-sm text-red-600';
            }
            
            // Reset input
            e.target.value = '';
        });

        // Remove background image
        async function removeBgImage() {
            if (!confirm('Remove background image?')) return;
            
            const statusEl = document.getElementById('bgImageStatus');
            statusEl.textContent = 'Removing...';
            statusEl.className = 'text-sm text-blue-600';
            
            try {
                const response = await fetch('upload-bg-image.php', {
                    method: 'DELETE'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    statusEl.textContent = 'Removed successfully!';
                    statusEl.className = 'text-sm text-green-600';
                    
                    // Hide preview
                    document.getElementById('bgImagePreview').classList.add('hidden');
                    
                    setTimeout(() => {
                        statusEl.textContent = '';
                    }, 3000);
                } else {
                    statusEl.textContent = 'Error: ' + data.error;
                    statusEl.className = 'text-sm text-red-600';
                }
            } catch (error) {
                statusEl.textContent = 'Remove failed';
                statusEl.className = 'text-sm text-red-600';
            }
        }

        // Password change form
        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const messageDiv = document.getElementById('passwordMessage');
            
            // Validate passwords match
            if (newPassword !== confirmPassword) {
                showMessage('New passwords do not match', 'error');
                return;
            }
            
            try {
                const response = await fetch(`${BASE_URL}/admin-change-password.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        current_password: currentPassword,
                        new_password: newPassword
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Password changed successfully!', 'success');
                    document.getElementById('passwordForm').reset();
                } else {
                    showMessage(data.error || 'Failed to change password', 'error');
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
            }
        });
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('passwordMessage');
            messageDiv.textContent = message;
            messageDiv.className = type === 'success' 
                ? 'mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded'
                : 'mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded';
            messageDiv.classList.remove('hidden');
            
            if (type === 'success') {
                setTimeout(() => {
                    messageDiv.classList.add('hidden');
                }, 3000);
            }
        }
    </script>
</body>
</html>
