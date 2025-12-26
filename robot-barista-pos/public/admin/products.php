<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

// Upload directory
$uploadDir = __DIR__ . '/../../public/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Get exchange rate
$sql = "SELECT setting_value FROM settings WHERE setting_key = 'exchange_rate_usd_to_khr'";
$stmt = $db->query($sql);
$exchangeRate = $stmt->fetchColumn() ?: 4100;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            // Handle image upload
            $imageName = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageName = time() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $imageName;
                move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
            }
            
            // Calculate KHR price from USD
            $priceUSD = floatval($_POST['price_usd']);
            $priceKHR = $priceUSD * $exchangeRate;
            
            $sql = "INSERT INTO products (category_id, name, description, image, price_usd, price_khr, is_available, has_modifiers, display_order) 
                    VALUES (:category_id, :name, :description, :image, :price_usd, :price_khr, :is_available, :has_modifiers, :display_order)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':category_id' => $_POST['category_id'],
                ':name' => $_POST['name'],
                ':description' => $_POST['description'],
                ':image' => $imageName,
                ':price_usd' => $priceUSD,
                ':price_khr' => $priceKHR,
                ':is_available' => isset($_POST['is_available']) ? 1 : 0,
                ':has_modifiers' => isset($_POST['has_modifiers']) ? 1 : 0,
                ':display_order' => $_POST['display_order'] ?? 0
            ]);
            $success = "Product added successfully!";
            
        } elseif ($_POST['action'] === 'edit') {
            // Get old image
            $sql = "SELECT image FROM products WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $_POST['id']]);
            $oldProduct = $stmt->fetch();
            $oldImage = $oldProduct['image'];
            
            // Handle new image upload
            $imageName = $oldImage;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Delete old image
                if ($oldImage && file_exists($uploadDir . $oldImage)) {
                    unlink($uploadDir . $oldImage);
                }
                // Upload new image
                $imageName = time() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $imageName;
                move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
            }
            
            // Calculate KHR price from USD
            $priceUSD = floatval($_POST['price_usd']);
            $priceKHR = $priceUSD * $exchangeRate;
            
            $sql = "UPDATE products SET 
                    category_id = :category_id,
                    name = :name,
                    description = :description,
                    image = :image,
                    price_usd = :price_usd,
                    price_khr = :price_khr,
                    is_available = :is_available,
                    has_modifiers = :has_modifiers,
                    display_order = :display_order
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':category_id' => $_POST['category_id'],
                ':name' => $_POST['name'],
                ':description' => $_POST['description'],
                ':image' => $imageName,
                ':price_usd' => $priceUSD,
                ':price_khr' => $priceKHR,
                ':is_available' => isset($_POST['is_available']) ? 1 : 0,
                ':has_modifiers' => isset($_POST['has_modifiers']) ? 1 : 0,
                ':display_order' => $_POST['display_order'] ?? 0,
                ':id' => $_POST['id']
            ]);
            $success = "Product updated successfully!";
            
        } elseif ($_POST['action'] === 'delete') {
            // Get image before deleting
            $sql = "SELECT image FROM products WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $_POST['id']]);
            $product = $stmt->fetch();
            
            // Delete image file
            if ($product && $product['image'] && file_exists($uploadDir . $product['image'])) {
                unlink($uploadDir . $product['image']);
            }
            
            // Delete product
            $sql = "DELETE FROM products WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $_POST['id']]);
            $success = "Product deleted successfully!";
        }
    }
}

// Get all products
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.display_order, p.name";
$stmt = $db->query($sql);
$products = $stmt->fetchAll();

// Get categories for dropdown
$sql = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$stmt = $db->query($sql);
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Robot Barista Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto lg:ml-0">
            <header class="bg-white shadow-sm p-4 pl-16 lg:pl-4 flex justify-between items-center">
                <h2 class="text-lg lg:text-2xl font-bold">Products</h2>
                <button onclick="showAddModal()" class="bg-green-600 text-white px-3 lg:px-4 py-2 rounded-lg hover:bg-green-700 text-sm lg:text-base">
                    <i class="fas fa-plus"></i> <span class="hidden sm:inline">Add</span>
                </button>
            </header>

            <div class="p-6">
                <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto -mx-4 lg:mx-0">
                        <table class="w-full min-w-[800px]">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Category</th>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Status</th>
                                    <th class="px-2 lg:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($products as $product): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 lg:px-4 py-3">
                                        <?php if ($product['image']): ?>
                                        <img src="../../public/uploads/<?= htmlspecialchars($product['image']) ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>"
                                             class="w-12 h-12 lg:w-16 lg:h-16 object-cover rounded">
                                        <?php else: ?>
                                        <div class="w-12 h-12 lg:w-16 lg:h-16 bg-gray-200 rounded flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400 text-sm"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-2 lg:px-4 py-3 font-medium text-sm"><?= htmlspecialchars($product['name']) ?></td>
                                    <td class="px-2 lg:px-4 py-3 text-sm hidden md:table-cell"><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                                    <td class="px-2 lg:px-4 py-3 text-sm">$<?= number_format($product['price_usd'], 2) ?></td>
                                    <td class="px-2 lg:px-4 py-3 hidden sm:table-cell">
                                        <span class="px-2 py-1 rounded text-xs <?= $product['is_available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $product['is_available'] ? 'Available' : 'Unavailable' ?>
                                        </span>
                                    </td>
                                    <td class="px-2 lg:px-4 py-3">
                                        <button onclick='editProduct(<?= json_encode($product) ?>)' class="text-blue-600 hover:text-blue-800 mr-2">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this product?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <form method="POST" enctype="multipart/form-data" class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="modalTitle" class="text-2xl font-bold">Add Product</h3>
                    <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="productId">

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold mb-2">Product Image</label>
                        <input type="file" name="image" id="productImage" accept="image/*" 
                               class="w-full border-2 border-gray-300 rounded-lg px-4 py-2"
                               onchange="previewImage(this)">
                        <div id="imagePreview" class="mt-2 hidden">
                            <img id="previewImg" src="" alt="Preview" class="w-32 h-32 object-cover rounded">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Recommended: 400x400px, JPG or PNG</p>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Product Name *</label>
                        <input type="text" name="name" id="productName" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Category *</label>
                        <select name="category_id" id="productCategory" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Description</label>
                        <textarea name="description" id="productDescription" rows="3" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2"></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Price USD *</label>
                        <input type="number" step="0.01" name="price_usd" id="productPriceUSD" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2" onchange="calculateKHRPrice()">
                        <p class="text-xs text-gray-500 mt-1">KHR price will be calculated automatically based on exchange rate</p>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Display Order</label>
                        <input type="number" name="display_order" id="productOrder" value="0" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_available" id="productAvailable" checked class="mr-2">
                            <span class="font-semibold">Available for sale</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="has_modifiers" id="productHasModifiers" checked class="mr-2">
                            <span class="font-semibold">Allow size modifiers</span>
                        </label>
                        <p class="text-xs text-gray-500 ml-6">If unchecked, customers cannot select size for this product</p>
                    </div>
                </div>

                <div class="flex space-x-2 mt-6">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700">
                        Save Product
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Product';
            document.getElementById('formAction').value = 'add';
            document.getElementById('productId').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productCategory').value = '';
            document.getElementById('productDescription').value = '';
            document.getElementById('productPriceUSD').value = '';
            document.getElementById('productOrder').value = '0';
            document.getElementById('productAvailable').checked = true;
            document.getElementById('productHasModifiers').checked = true;
            document.getElementById('productImage').value = '';
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('productModal').classList.remove('hidden');
        }

        const exchangeRate = <?= $exchangeRate ?>;

        function calculateKHRPrice() {
            const usdPrice = parseFloat(document.getElementById('productPriceUSD').value) || 0;
            const khrPrice = Math.round(usdPrice * exchangeRate);
            console.log('USD: $' + usdPrice.toFixed(2) + ' = KHR: ៛' + khrPrice.toLocaleString());
        }

        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productCategory').value = product.category_id;
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('productPriceUSD').value = product.price_usd;
            document.getElementById('productOrder').value = product.display_order;
            document.getElementById('productAvailable').checked = product.is_available == 1;
            document.getElementById('productHasModifiers').checked = product.has_modifiers == 1;
            document.getElementById('productImage').value = '';
            
            // Show current image if exists
            if (product.image) {
                document.getElementById('previewImg').src = '../../public/uploads/' + product.image;
                document.getElementById('imagePreview').classList.remove('hidden');
            } else {
                document.getElementById('imagePreview').classList.add('hidden');
            }
            
            document.getElementById('productModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
