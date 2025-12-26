<!-- Mobile Menu Button -->
<button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 bg-gray-800 text-white p-3 rounded-lg shadow-lg">
    <i class="fas fa-bars text-xl"></i>
</button>

<!-- Sidebar -->
<aside id="sidebar" class="fixed lg:static inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out w-64 bg-gray-800 text-white z-40">
    <div class="p-4">
        <h1 class="text-xl font-bold"><i class="fas fa-robot"></i> Robot Barista</h1>
        <p class="text-sm text-gray-400">Admin Panel</p>
        <p class="text-xs text-gray-500 mt-1">Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></p>
    </div>
    <nav class="mt-4">
        <a href="dashboard.php" class="block px-4 py-3 hover:bg-gray-700 <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-gray-700' : '' ?>">
            <i class="fas fa-dashboard"></i> Dashboard
        </a>
        <a href="products.php" class="block px-4 py-3 hover:bg-gray-700 <?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'bg-gray-700' : '' ?>">
            <i class="fas fa-coffee"></i> Products
        </a>
        <a href="categories.php" class="block px-4 py-3 hover:bg-gray-700 <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'bg-gray-700' : '' ?>">
            <i class="fas fa-list"></i> Categories
        </a>
        <a href="modifiers.php" class="block px-4 py-3 hover:bg-gray-700 <?= basename($_SERVER['PHP_SELF']) === 'modifiers.php' ? 'bg-gray-700' : '' ?>">
            <i class="fas fa-sliders-h"></i> Modifiers
        </a>
        <a href="orders.php" class="block px-4 py-3 hover:bg-gray-700 <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'bg-gray-700' : '' ?>">
            <i class="fas fa-receipt"></i> Orders
        </a>
        <a href="reports.php" class="block px-4 py-3 hover:bg-gray-700 <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'bg-gray-700' : '' ?>">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
        <a href="settings.php" class="block px-4 py-3 hover:bg-gray-700 <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'bg-gray-700' : '' ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
        <a href="users.php" class="block px-4 py-3 hover:bg-gray-700 <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'bg-gray-700' : '' ?>">
            <i class="fas fa-users"></i> Users
        </a>
        <a href="logout.php" class="block px-4 py-3 hover:bg-gray-700 mt-4 border-t border-gray-700">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</aside>

<!-- Overlay for mobile -->
<div id="sidebarOverlay" class="hidden lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30"></div>

<script>
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');

mobileMenuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
});

overlay.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
});
</script>
