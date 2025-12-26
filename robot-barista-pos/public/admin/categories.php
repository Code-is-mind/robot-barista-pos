<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $sql = "INSERT INTO categories (name, description, icon, display_order, is_active) 
                    VALUES (:name, :description, :icon, :display_order, :is_active)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $_POST['name'],
                ':description' => $_POST['description'],
                ':icon' => $_POST['icon'] ?? 'fa-coffee',
                ':display_order' => $_POST['display_order'] ?? 0,
                ':is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            $success = "Category added successfully!";
        } elseif ($_POST['action'] === 'edit') {
            $sql = "UPDATE categories SET 
                    name = :name,
                    description = :description,
                    icon = :icon,
                    display_order = :display_order,
                    is_active = :is_active
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $_POST['name'],
                ':description' => $_POST['description'],
                ':icon' => $_POST['icon'] ?? 'fa-coffee',
                ':display_order' => $_POST['display_order'] ?? 0,
                ':is_active' => isset($_POST['is_active']) ? 1 : 0,
                ':id' => $_POST['id']
            ]);
            $success = "Category updated successfully!";
        } elseif ($_POST['action'] === 'delete') {
            $sql = "DELETE FROM categories WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $_POST['id']]);
            $success = "Category deleted successfully!";
        }
    }
}

// Get all categories with product count
$sql = "SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.display_order, c.name";
$stmt = $db->query($sql);
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Robot Barista Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto lg:ml-0">
            <header class="bg-white shadow-sm p-4 pl-16 lg:pl-4 flex justify-between items-center">
                <h2 class="text-lg lg:text-2xl font-bold">Categories</h2>
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

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($categories as $category): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold"><?= htmlspecialchars($category['name']) ?></h3>
                                <p class="text-sm text-gray-600"><?= $category['product_count'] ?> products</p>
                            </div>
                            <span class="px-2 py-1 rounded text-xs <?= $category['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                        <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($category['description'] ?? 'No description') ?></p>
                        <div class="flex space-x-2">
                            <button onclick='editCategory(<?= json_encode($category) ?>)' class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" class="flex-1" onsubmit="return confirm('Delete this category? All products in this category will also be deleted.')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="categoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-lg w-full">
            <form method="POST" class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="modalTitle" class="text-2xl font-bold">Add Category</h3>
                    <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="categoryId">

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold mb-2">Category Name *</label>
                        <input type="text" name="name" id="categoryName" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Description</label>
                        <textarea name="description" id="categoryDescription" rows="3" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2"></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Icon</label>
                        <div class="flex items-center space-x-2">
                            <i id="iconPreview" class="fas fa-coffee text-2xl text-gray-600"></i>
                            <select name="icon" id="categoryIcon" class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2" onchange="updateIconPreview()">
                                <option value="fa-coffee">Coffee Cup (fa-coffee)</option>
                                <option value="fa-mug-hot">Hot Mug (fa-mug-hot)</option>
                                <option value="fa-leaf">Leaf (fa-leaf)</option>
                                <option value="fa-glass-water">Glass Water (fa-glass-water)</option>
                                <option value="fa-wine-glass">Wine Glass (fa-wine-glass)</option>
                                <option value="fa-cocktail">Cocktail (fa-cocktail)</option>
                                <option value="fa-bread-slice">Bread Slice (fa-bread-slice)</option>
                                <option value="fa-cookie-bite">Cookie Bite (fa-cookie-bite)</option>
                                <option value="fa-cake-candles">Cake (fa-cake-candles)</option>
                                <option value="fa-pizza-slice">Pizza Slice (fa-pizza-slice)</option>
                                <option value="fa-hamburger">Hamburger (fa-hamburger)</option>
                                <option value="fa-hotdog">Hot Dog (fa-hotdog)</option>
                                <option value="fa-ice-cream">Ice Cream (fa-ice-cream)</option>
                                <option value="fa-apple-whole">Apple (fa-apple-whole)</option>
                                <option value="fa-carrot">Carrot (fa-carrot)</option>
                                <option value="fa-pepper-hot">Hot Pepper (fa-pepper-hot)</option>
                                <option value="fa-fish">Fish (fa-fish)</option>
                                <option value="fa-drumstick-bite">Drumstick (fa-drumstick-bite)</option>
                                <option value="fa-cheese">Cheese (fa-cheese)</option>
                                <option value="fa-egg">Egg (fa-egg)</option>
                                <option value="fa-utensils">Utensils (fa-utensils)</option>
                                <option value="fa-bowl-food">Bowl Food (fa-bowl-food)</option>
                                <option value="fa-plate-wheat">Plate (fa-plate-wheat)</option>
                                <option value="fa-candy-cane">Candy Cane (fa-candy-cane)</option>
                                <option value="fa-lemon">Lemon (fa-lemon)</option>
                                <option value="fa-seedling">Seedling (fa-seedling)</option>
                                <option value="fa-fire">Fire (fa-fire)</option>
                                <option value="fa-snowflake">Snowflake (fa-snowflake)</option>
                                <option value="fa-star">Star (fa-star)</option>
                                <option value="fa-heart">Heart (fa-heart)</option>
                                <option value="fa-gem">Gem (fa-gem)</option>
                                <option value="fa-crown">Crown (fa-crown)</option>
                            </select>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Choose an icon that represents this category</p>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Display Order</label>
                        <input type="number" name="display_order" id="categoryOrder" value="0" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" id="categoryActive" checked class="mr-2">
                            <span class="font-semibold">Active</span>
                        </label>
                    </div>
                </div>

                <div class="flex space-x-2 mt-6">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700">
                        Save Category
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateIconPreview() {
            const iconSelect = document.getElementById('categoryIcon');
            const iconPreview = document.getElementById('iconPreview');
            iconPreview.className = `fas ${iconSelect.value} text-2xl text-gray-600`;
        }

        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add Category';
            document.getElementById('formAction').value = 'add';
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryDescription').value = '';
            document.getElementById('categoryIcon').value = 'fa-coffee';
            document.getElementById('categoryOrder').value = '0';
            document.getElementById('categoryActive').checked = true;
            updateIconPreview();
            document.getElementById('categoryModal').classList.remove('hidden');
        }

        function editCategory(category) {
            document.getElementById('modalTitle').textContent = 'Edit Category';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description || '';
            document.getElementById('categoryIcon').value = category.icon || 'fa-coffee';
            document.getElementById('categoryOrder').value = category.display_order;
            document.getElementById('categoryActive').checked = category.is_active == 1;
            updateIconPreview();
            document.getElementById('categoryModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('categoryModal').classList.add('hidden');
        }
    </script>
</body>
</html>
