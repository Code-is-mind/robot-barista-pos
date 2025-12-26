// Simple Kiosk Application - Direct PHP files, no API routing
const BASE_URL = '/robot-barista-pos';
let currentCurrency = 'USD';
let cart = [];
let categories = [];
let products = [];
let selectedProduct = null;
let exchangeRate = 4100;

// Initialize
document.addEventListener('DOMContentLoaded', async () => {
    await loadSettings();
    await loadCategories();
    await loadProducts();
    setupEventListeners();
    updateCartUI();
});

async function loadSettings() {
    try {
        const response = await fetch(`${BASE_URL}/get-settings.php`);
        const data = await response.json();
        if (data.success) {
            const settings = {};
            data.data.forEach(s => settings[s.setting_key] = s.setting_value);
            exchangeRate = parseFloat(settings.exchange_rate_usd_to_khr) || 4100;
        }
    } catch (error) {
        console.error('Error loading settings:', error);
    }
}

async function loadCategories() {
    try {
        const response = await fetch(`${BASE_URL}/get-categories.php`);
        const data = await response.json();
        if (data.success) {
            categories = data.data;
            renderCategories();
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadProducts(categoryId = null) {
    try {
        const url = categoryId ? 
            `${BASE_URL}/get-products.php?category_id=${categoryId}` : 
            `${BASE_URL}/get-products.php`;
        const response = await fetch(url);
        const data = await response.json();
        if (data.success) {
            products = data.data;
            renderProducts();
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

function renderCategories() {
    const container = document.getElementById('categories');
    container.innerHTML = `
        <button class="category-btn px-6 py-3 rounded-lg font-semibold bg-orange-600 text-white whitespace-nowrap" data-id="">
            <i class="fas fa-th"></i> All
        </button>
    `;
    
    categories.forEach(cat => {
        const btn = document.createElement('button');
        btn.className = 'category-btn px-6 py-3 rounded-lg font-semibold bg-white hover:bg-orange-100 whitespace-nowrap';
        btn.dataset.id = cat.id;
        btn.innerHTML = `<i class="fas fa-coffee"></i> ${cat.name}`;
        container.appendChild(btn);
    });
    
    container.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            container.querySelectorAll('.category-btn').forEach(b => {
                b.className = 'category-btn px-6 py-3 rounded-lg font-semibold bg-white hover:bg-orange-100 whitespace-nowrap';
            });
            btn.className = 'category-btn px-6 py-3 rounded-lg font-semibold bg-orange-600 text-white whitespace-nowrap';
            loadProducts(btn.dataset.id || null);
        });
    });
}

function renderProducts() {
    const container = document.getElementById('productsGrid');
    container.innerHTML = '';
    
    if (products.length === 0) {
        container.innerHTML = '<p class="col-span-full text-center text-gray-500 py-8">No products available</p>';
        return;
    }
    
    products.forEach(product => {
        const price = currentCurrency === 'USD' ? product.price_usd : product.price_khr;
        const symbol = currentCurrency === 'USD' ? '$' : '៛';
        
        const card = document.createElement('div');
        card.className = 'product-card bg-white rounded-lg shadow-lg overflow-hidden cursor-pointer';
        card.innerHTML = `
            <img src="${product.image ? BASE_URL + '/public/uploads/' + product.image : 'https://via.placeholder.com/300x200?text=' + encodeURIComponent(product.name)}" 
                 class="w-full h-40 object-cover" alt="${product.name}">
            <div class="p-4">
                <h3 class="font-bold text-lg mb-2">${product.name}</h3>
                <p class="text-gray-600 text-sm mb-2 line-clamp-2">${product.description || ''}</p>
                <p class="text-orange-600 font-bold text-xl">${symbol}${formatPrice(price)}</p>
            </div>
        `;
        
        card.addEventListener('click', () => openProductModal(product));
        container.appendChild(card);
    });
}

function openProductModal(product) {
    selectedProduct = product;
    const modal = document.getElementById('productModal');
    
    document.getElementById('modalProductName').textContent = product.name;
    document.getElementById('modalProductDesc').textContent = product.description || '';
    document.getElementById('modalProductImage').src = product.image ? 
        BASE_URL + '/public/uploads/' + product.image : 
        'https://via.placeholder.com/400x300?text=' + encodeURIComponent(product.name);
    document.getElementById('modalQty').textContent = '1';
    
    // Reset size selection to Medium
    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.className = 'size-btn flex-1 border-2 border-gray-300 py-2 rounded-lg hover:border-orange-500';
    });
    document.querySelectorAll('.size-btn')[1].className = 'size-btn flex-1 border-2 border-orange-500 py-2 rounded-lg bg-orange-50';
    
    updateModalPrice();
    modal.classList.remove('hidden');
}

function updateModalPrice() {
    const basePrice = currentCurrency === 'USD' ? selectedProduct.price_usd : selectedProduct.price_khr;
    const sizeBtn = document.querySelector('.size-btn.bg-orange-50');
    const sizePrice = parseFloat(sizeBtn?.dataset.price || 0);
    const sizePriceConverted = currentCurrency === 'USD' ? sizePrice : sizePrice * exchangeRate;
    const qty = parseInt(document.getElementById('modalQty').textContent);
    
    const totalPrice = (parseFloat(basePrice) + sizePriceConverted) * qty;
    const symbol = currentCurrency === 'USD' ? '$' : '៛';
    
    document.getElementById('modalPrice').textContent = `${symbol}${formatPrice(totalPrice)}`;
}

function setupEventListeners() {
    // Currency toggle
    document.getElementById('currencyToggle').addEventListener('click', () => {
        currentCurrency = currentCurrency === 'USD' ? 'KHR' : 'USD';
        const btn = document.getElementById('currencyToggle');
        btn.innerHTML = currentCurrency === 'USD' ? 
            '<i class="fas fa-dollar-sign"></i> USD' : 
            '<i class="fas fa-coins"></i> KHR';
        renderProducts();
        updateCartUI();
        if (!document.getElementById('productModal').classList.contains('hidden')) {
            updateModalPrice();
        }
    });
    
    // Cart button
    document.getElementById('cartBtn').addEventListener('click', () => {
        document.getElementById('cartModal').classList.remove('hidden');
    });
    
    document.getElementById('closeCart').addEventListener('click', () => {
        document.getElementById('cartModal').classList.add('hidden');
    });
    
    // Product modal
    document.getElementById('closeProductModal').addEventListener('click', () => {
        document.getElementById('productModal').classList.add('hidden');
    });
    
    // Size selection
    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.size-btn').forEach(b => {
                b.className = 'size-btn flex-1 border-2 border-gray-300 py-2 rounded-lg hover:border-orange-500';
            });
            btn.className = 'size-btn flex-1 border-2 border-orange-500 py-2 rounded-lg bg-orange-50';
            updateModalPrice();
        });
    });
    
    // Quantity
    document.getElementById('decreaseQty').addEventListener('click', () => {
        const qtyEl = document.getElementById('modalQty');
        let qty = parseInt(qtyEl.textContent);
        if (qty > 1) {
            qtyEl.textContent = qty - 1;
            updateModalPrice();
        }
    });
    
    document.getElementById('increaseQty').addEventListener('click', () => {
        const qtyEl = document.getElementById('modalQty');
        let qty = parseInt(qtyEl.textContent);
        qtyEl.textContent = qty + 1;
        updateModalPrice();
    });
    
    // Add to cart
    document.getElementById('addToCartBtn').addEventListener('click', addToCart);
    
    // Clear cart
    document.getElementById('clearCart').addEventListener('click', () => {
        if (confirm('Clear all items from cart?')) {
            cart = [];
            updateCartUI();
        }
    });
    
    // Checkout
    document.getElementById('checkoutBtn').addEventListener('click', () => {
        if (cart.length === 0) {
            alert('Your cart is empty!');
            return;
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        localStorage.setItem('currency', currentCurrency);
        window.location.href = 'checkout.html';
    });
}

function addToCart() {
    const sizeBtn = document.querySelector('.size-btn.bg-orange-50');
    const size = sizeBtn.dataset.size;
    const sizePrice = parseFloat(sizeBtn.dataset.price);
    const sizePriceConverted = currentCurrency === 'USD' ? sizePrice : sizePrice * exchangeRate;
    const qty = parseInt(document.getElementById('modalQty').textContent);
    
    const basePrice = currentCurrency === 'USD' ? selectedProduct.price_usd : selectedProduct.price_khr;
    const unitPrice = parseFloat(basePrice) + sizePriceConverted;
    
    cart.push({
        product_id: selectedProduct.id,
        product_name: selectedProduct.name,
        quantity: qty,
        unit_price: unitPrice,
        modifiers: { size },
        subtotal: unitPrice * qty
    });
    
    updateCartUI();
    document.getElementById('productModal').classList.add('hidden');
    
    // Show success feedback
    const cartCount = document.getElementById('cartCount');
    cartCount.classList.add('cart-badge');
    setTimeout(() => cartCount.classList.remove('cart-badge'), 500);
}

function updateCartUI() {
    const cartCount = document.getElementById('cartCount');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    if (totalItems > 0) {
        cartCount.textContent = totalItems;
        cartCount.classList.remove('hidden');
    } else {
        cartCount.classList.add('hidden');
    }
    
    // Update cart modal
    const cartItems = document.getElementById('cartItems');
    const symbol = currentCurrency === 'USD' ? '$' : '៛';
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="text-gray-500 text-center py-8">Your cart is empty</p>';
    } else {
        cartItems.innerHTML = cart.map((item, index) => `
            <div class="flex justify-between items-center border-b pb-2">
                <div class="flex-1">
                    <p class="font-semibold">${item.product_name}</p>
                    <p class="text-sm text-gray-600">Size: ${item.modifiers.size} × ${item.quantity}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="font-semibold">${symbol}${formatPrice(item.subtotal)}</span>
                    <button class="text-red-500 hover:text-red-700" onclick="removeFromCart(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const tax = subtotal * 0.1;
    const total = subtotal + tax;
    
    document.getElementById('cartSubtotal').textContent = `${symbol}${formatPrice(subtotal)}`;
    document.getElementById('cartTax').textContent = `${symbol}${formatPrice(tax)}`;
    document.getElementById('cartTotal').textContent = `${symbol}${formatPrice(total)}`;
}

window.removeFromCart = (index) => {
    cart.splice(index, 1);
    updateCartUI();
};

function formatPrice(price) {
    return currentCurrency === 'USD' ? 
        parseFloat(price).toFixed(2) : 
        Math.round(price).toLocaleString();
}
