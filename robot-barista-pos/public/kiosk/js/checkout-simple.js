// Simple Checkout - Direct PHP files, no API routing
const BASE_URL = '/robot-barista-pos';
let cart = [];
let currency = 'USD';
let selectedPaymentMethod = 'KHQR';
let currentOrderId = null;
let currentOrderNumber = null;

document.addEventListener('DOMContentLoaded', () => {
    loadCartData();
    setupEventListeners();
    displayOrderSummary();
});

function loadCartData() {
    cart = JSON.parse(localStorage.getItem('cart') || '[]');
    currency = localStorage.getItem('currency') || 'USD';
    
    if (cart.length === 0) {
        alert('Your cart is empty!');
        window.location.href = 'index.html';
    }
}

function displayOrderSummary() {
    const container = document.getElementById('orderSummary');
    const symbol = currency === 'USD' ? '$' : '៛';
    
    container.innerHTML = cart.map(item => `
        <div class="flex justify-between">
            <span>${item.product_name} (${item.modifiers.size}) × ${item.quantity}</span>
            <span>${symbol}${formatPrice(item.subtotal)}</span>
        </div>
    `).join('');
    
    const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const tax = subtotal * 0.1;
    const total = subtotal + tax;
    
    document.getElementById('summarySubtotal').textContent = `${symbol}${formatPrice(subtotal)}`;
    document.getElementById('summaryTax').textContent = `${symbol}${formatPrice(tax)}`;
    document.getElementById('summaryTotal').textContent = `${symbol}${formatPrice(total)}`;
}

function setupEventListeners() {
    // Payment method is always KHQR
    selectedPaymentMethod = 'KHQR';
    
    // Place order
    document.getElementById('placeOrderBtn').addEventListener('click', placeOrder);
    
    // KHQR modal
    document.getElementById('confirmPaymentBtn').addEventListener('click', confirmPayment);
    document.getElementById('cancelPaymentBtn').addEventListener('click', () => {
        document.getElementById('khqrModal').classList.add('hidden');
    });
    
    // Receipt modal
    document.getElementById('printReceiptBtn').addEventListener('click', printReceipt);
    document.getElementById('skipReceiptBtn').addEventListener('click', skipReceipt);
}

async function placeOrder() {
    const customerName = document.getElementById('customerName').value.trim() || 'Walk-In Customer';
    
    const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const tax = subtotal * 0.1;
    const total = subtotal + tax;
    
    const orderData = {
        customer_name: customerName,
        currency: currency,
        subtotal: subtotal,
        tax_amount: tax,
        total_amount: total,
        payment_method: 'KHQR',
        items: cart
    };
    
    try {
        const response = await fetch(`${BASE_URL}/create-order.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentOrderId = data.data.order_id;
            currentOrderNumber = data.data.order_number;
            
            // Show KHQR payment
            showKHQRModal(total);
        } else {
            alert('Error placing order: ' + data.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error placing order. Please try again.');
    }
}

function showKHQRModal(amount) {
    const symbol = currency === 'USD' ? '$' : '៛';
    const amountKHR = currency === 'USD' ? amount * 4100 : amount;
    
    document.getElementById('qrAmount').textContent = `${symbol}${formatPrice(amount)}`;
    document.getElementById('qrOrderNumber').textContent = currentOrderNumber;
    
    // Generate QR code
    const qrData = `khqr://pay?amount=${amountKHR}&ref=${currentOrderNumber}`;
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(qrData)}`;
    
    document.getElementById('qrCodeImage').src = qrUrl;
    document.getElementById('khqrModal').classList.remove('hidden');
}

async function confirmPayment() {
    try {
        // Update payment status
        const response = await fetch(`${BASE_URL}/update-order.php?id=${currentOrderId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ payment_status: 'Paid' })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('khqrModal').classList.add('hidden');
            document.getElementById('receiptModal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error confirming payment');
    }
}

function printReceipt() {
    // Open print window
    window.open(`${BASE_URL}/print-receipt.php?order_id=${currentOrderId}`, '_blank');
    showRobotAnimation();
}

function skipReceipt() {
    showRobotAnimation();
}

function showRobotAnimation() {
    document.getElementById('receiptModal').classList.add('hidden');
    document.getElementById('robotModal').classList.remove('hidden');
    
    // Simulate robot preparation (5 seconds)
    setTimeout(() => {
        // Clear cart and redirect
        localStorage.removeItem('cart');
        localStorage.removeItem('currency');
        
        alert('Your order is ready! Order #' + currentOrderNumber);
        window.location.href = 'index.html';
    }, 5000);
}

function formatPrice(price) {
    return currency === 'USD' ? 
        parseFloat(price).toFixed(2) : 
        Math.round(price).toLocaleString();
}
