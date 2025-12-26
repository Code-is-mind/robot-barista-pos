<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

// Get exchange rate
$sql = "SELECT setting_value FROM settings WHERE setting_key = 'exchange_rate_usd_to_khr'";
$stmt = $db->query($sql);
$exchangeRate = $stmt->fetchColumn() ?: 4100;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $priceUSD = floatval($_POST['price_usd']);
            $priceKHR = $priceUSD * $exchangeRate;
            
            $sql = "INSERT INTO modifiers (name, type, price_usd, price_khr, is_active) 
                    VALUES (:name, :type, :price_usd, :price_khr, :is_active)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $_POST['name'],
                ':type' => $_POST['type'],
                ':price_usd' => $priceUSD,
                ':price_khr' => $priceKHR,
                ':is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            $success = "Modifier added successfully!";
            
        } elseif ($_POST['action'] === 'edit') {
            $priceUSD = floatval($_POST['price_usd']);
            $priceKHR = $priceUSD * $exchangeRate;
            
            $sql = "UPDATE modifiers SET 
                    name = :name,
                    type = :type,
                    price_usd = :price_usd,
                    price_khr = :price_khr,
                    is_active = :is_active
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $_POST['name'],
                ':type' => $_POST['type'],
                ':price_usd' => $priceUSD,
                ':price_khr' => $priceKHR,
                ':is_active' => isset($_POST['is_active']) ? 1 : 0,
                ':id' => $_POST['id']
            ]);
            $success = "Modifier updated successfully!";
            
        } elseif ($_POST['action'] === 'delete') {
            $sql = "DELETE FROM modifiers WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $_POST['id']]);
            $success = "Modifier deleted successfully!";
        }
    }
}

// Get all modifiers grouped by type
$sql = "SELECT * FROM modifiers ORDER BY type, price_usd";
$stmt = $db->query($sql);
$allModifiers = $stmt->fetchAll();

$modifiersByType = [];
foreach ($allModifiers as $mod) {
    $modifiersByType[$mod['type']][] = $mod;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifiers - Robot Barista Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto lg:ml-0">
            <header class="bg-white shadow-sm p-4 pl-16 lg:pl-4 flex justify-between items-center">
                <h2 class="text-lg lg:text-2xl font-bold">Modifiers Management</h2>
                <button onclick="showAddModal()" class="bg-green-600 text-white px-3 lg:px-4 py-2 rounded-lg hover:bg-green-700 text-sm lg:text-base">
                    <i class="fas fa-plus"></i> <span class="hidden sm:inline">Add Modifier</span>
                </button>
            </header>

            <div class="p-4 lg:p-6">
                <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <?php foreach ($modifiersByType as $type => $modifiers): ?>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-lg font-bold mb-3 capitalize">
                            <i class="fas fa-<?= $type === 'size' ? 'arrows-alt-v' : ($type === 'topping' ? 'ice-cream' : 'adjust') ?>"></i>
                            <?= ucfirst($type) ?>s
                        </h3>
                        <div class="space-y-2">
                            <?php foreach ($modifiers as $mod): ?>
                            <div class="flex justify-between items-center border-b pb-2">
                                <div class="flex-1">
                                    <p class="font-semibold"><?= htmlspecialchars($mod['name']) ?></p>
                                    <p class="text-sm text-gray-600">
                                        $<?= number_format($mod['price_usd'], 2) ?> / ៛<?= number_format($mod['price_khr'], 0) ?>
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 rounded text-xs <?= $mod['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $mod['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                    <button onclick='editModifier(<?= json_encode($mod) ?>)' class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this modifier?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $mod['id'] ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="modifierModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-lg w-full">
            <form method="POST" class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="modalTitle" class="text-2xl font-bold">Add Modifier</h3>
                    <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="modifierId">

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold mb-2">Name *</label>
                        <input type="text" name="name" id="modifierName" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Type *</label>
                        <select name="type" id="modifierType" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            <option value="size">Size</option>
                            <option value="topping">Topping</option>
                            <option value="sugar">Sugar Level</option>
                            <option value="ice">Ice Level</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Price USD *</label>
                        <input type="number" step="0.01" name="price_usd" id="modifierPrice" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                        <p class="text-xs text-gray-500 mt-1">KHR price: ៛<span id="khrPreview">0</span> (auto-calculated)</p>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" id="modifierActive" checked class="mr-2">
                            <span class="font-semibold">Active</span>
                        </label>
                    </div>
                </div>

                <div class="flex space-x-2 mt-6">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700">
                        Save Modifier
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const exchangeRate = <?= $exchangeRate ?>;

        document.getElementById('modifierPrice').addEventListener('input', function() {
            const usdPrice = parseFloat(this.value) || 0;
            const khrPrice = Math.round(usdPrice * exchangeRate);
            document.getElementById('khrPreview').textContent = khrPrice.toLocaleString();
        });

        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Modifier';
            document.getElementById('formAction').value = 'add';
            document.getElementById('modifierId').value = '';
            document.getElementById('modifierName').value = '';
            document.getElementById('modifierType').value = 'size';
            document.getElementById('modifierPrice').value = '';
            document.getElementById('modifierActive').checked = true;
            document.getElementById('khrPreview').textContent = '0';
            document.getElementById('modifierModal').classList.remove('hidden');
        }

        function editModifier(modifier) {
            document.getElementById('modalTitle').textContent = 'Edit Modifier';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('modifierId').value = modifier.id;
            document.getElementById('modifierName').value = modifier.name;
            document.getElementById('modifierType').value = modifier.type;
            document.getElementById('modifierPrice').value = modifier.price_usd;
            document.getElementById('modifierActive').checked = modifier.is_active == 1;
            document.getElementById('khrPreview').textContent = Math.round(modifier.price_usd * exchangeRate).toLocaleString();
            document.getElementById('modifierModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modifierModal').classList.add('hidden');
        }
    </script>
</body>
</html>
