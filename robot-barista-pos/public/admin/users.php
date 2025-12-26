<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
$db = Database::getInstance()->getConnection();

// Check if current user is root/admin
$currentUserId = $_SESSION['admin_user_id'] ?? 0;
$sql = "SELECT role FROM users WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $currentUserId]);
$currentUser = $stmt->fetch();
$isRoot = ($currentUser && $currentUser['role'] === 'admin');

// Get all users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $db->query($sql);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Robot Barista Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto lg:ml-0">
            <header class="bg-white shadow-sm p-4 pl-16 lg:pl-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl lg:text-2xl font-bold">User Management</h2>
                    <?php if ($isRoot): ?>
                    <button onclick="openAddModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                    <?php endif; ?>
                </div>
            </header>

            <div class="p-6">
                <?php if (!$isRoot): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                    <p class="font-bold">Limited Access</p>
                    <p>Only root administrators can add or modify users.</p>
                </div>
                <?php endif; ?>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Full Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                <?php if ($isRoot): ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm"><?= $user['id'] ?></td>
                                <td class="px-6 py-4 text-sm font-medium"><?= htmlspecialchars($user['username']) ?></td>
                                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($user['full_name'] ?? '-') ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        <?= $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 
                                            ($user['role'] === 'manager' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        <?= $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never' ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                </td>
                                <?php if ($isRoot): ?>
                                <td class="px-6 py-4 text-sm">
                                    <button onclick='editUser(<?= json_encode($user) ?>)' 
                                            class="text-blue-600 hover:text-blue-800 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['id'] != $currentUserId): ?>
                                    <button onclick="toggleStatus(<?= $user['id'] ?>, <?= $user['is_active'] ?>)" 
                                            class="text-<?= $user['is_active'] ? 'red' : 'green' ?>-600 hover:text-<?= $user['is_active'] ? 'red' : 'green' ?>-800 mr-3">
                                        <i class="fas fa-<?= $user['is_active'] ? 'ban' : 'check' ?>"></i>
                                    </button>
                                    <button onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')" 
                                            class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg max-w-md w-full mx-4 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold" id="modalTitle">Add User</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <form id="userForm">
                <input type="hidden" id="userId" name="id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Username *</label>
                    <input type="text" id="username" name="username" required
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-green-500 focus:outline-none">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Full Name</label>
                    <input type="text" id="fullName" name="full_name"
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-green-500 focus:outline-none">
                </div>

                <div class="mb-4" id="passwordField">
                    <label class="block text-gray-700 font-semibold mb-2">Password *</label>
                    <input type="password" id="password" name="password"
                           class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-green-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password (when editing)</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Role *</label>
                    <select id="role" name="role" required
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-green-500 focus:outline-none">
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin (Root)</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" id="isActive" name="is_active" checked class="mr-2">
                        <span class="text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add User';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('password').required = true;
            document.getElementById('userModal').classList.remove('hidden');
        }

        function editUser(user) {
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('userId').value = user.id;
            document.getElementById('username').value = user.username;
            document.getElementById('fullName').value = user.full_name || '';
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            document.getElementById('role').value = user.role;
            document.getElementById('isActive').checked = user.is_active == 1;
            document.getElementById('userModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        // Save user
        $('#userForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                id: $('#userId').val(),
                username: $('#username').val(),
                full_name: $('#fullName').val(),
                password: $('#password').val(),
                role: $('#role').val(),
                is_active: $('#isActive').is(':checked') ? 1 : 0
            };

            $.ajax({
                url: 'users_api.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ action: 'save', data: formData }),
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Failed to save user');
                }
            });
        });

        // Toggle user status
        function toggleStatus(userId, currentStatus) {
            const newStatus = currentStatus ? 0 : 1;
            const action = newStatus ? 'activate' : 'deactivate';
            
            if (!confirm(`Are you sure you want to ${action} this user?`)) return;

            $.ajax({
                url: 'users_api.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ action: 'toggle_status', id: userId, status: newStatus }),
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Failed to update user status');
                }
            });
        }

        // Delete user
        function deleteUser(userId, username) {
            if (!confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) return;

            $.ajax({
                url: 'users_api.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ action: 'delete', id: userId }),
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Failed to delete user');
                }
            });
        }
    </script>
</body>
</html>
