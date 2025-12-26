# 🗑️ Removed Files & 📍 API Locations

## ❌ Files Removed (No Longer Needed)

### 1. `public/kiosk/cart.php` ✅ DELETED
**Why:** No cart system anymore - direct purchase only

**Old functionality:**
- Display cart items
- Update quantities
- Remove items
- Calculate totals
- Proceed to checkout

**New approach:**
- Order directly from product page
- No cart needed

---

### 2. `public/kiosk/checkout.php` ✅ DELETED
**Why:** Payment integrated into product page

**Old functionality:**
- Review order summary
- Enter customer name
- Select payment method
- Place order

**New approach:**
- All done in product.php modal
- Streamlined single-page flow

---

### 3. `public/kiosk/payment.php` ✅ DELETED
**Why:** Payment modal now in product.php

**Old functionality:**
- Show QR code
- Wait for payment
- Redirect to success

**New approach:**
- Payment modal in product.php
- AJAX-based status checking
- No page redirects

---

## 📍 Payment API Locations

### 🔑 Location 1: API Token (MOST IMPORTANT)

**File:** `config/payment.php` ⚠️ SECURE LOCATION  
**Line:** 11  
**Purpose:** Verify payment with Bakong API

```php
// Line 10-11
define('BAKONG_API_URL', 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5');
define('BAKONG_API_TOKEN', 'YOUR_TOKEN_HERE');
```

**When to update:**
- Token expires (check expiration date)
- Get new API credentials from NBC
- Security rotation

**How to check expiration:**
1. Go to https://jwt.io
2. Paste your token
3. Look at `exp` field (Unix timestamp)
4. Current token expires: April 13, 2026

---

### 🏪 Location 2: Merchant Information

**File:** `config/payment.php` ⚠️ SECURE LOCATION  
**Lines:** 14-17  
**Purpose:** Generate KHQR with your business details

```php
// Lines 14-17
define('MERCHANT_ACCOUNT_ID', 'your_account@bankcode');
define('MERCHANT_NAME', 'Your Business Name');
define('MERCHANT_CITY', 'Your City');
define('MERCHANT_MOBILE', '012345678');
```

**When to update:**
- Change business name
- Change Bakong account
- Update location

**Format:**
- Account ID: `username@bankcode` (e.g., `john@aclb`)
- Name: Your registered business name
- City: Your business location

---

### 🌐 Location 3: API Endpoint

**File:** `payment/check_transaction.php`  
**Line:** 20  
**Purpose:** Bakong API URL

```php
$url = 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5';
```

**When to update:**
- NBC releases new API version (v2, v3, etc.)
- API endpoint changes

**Current version:** v1

---

## 📂 Current File Structure

### Active Kiosk Files:
```
public/kiosk/
├── index.php                 # Product listing
├── product.php               # 🔑 Main ordering page (has merchant info)
├── create_order.php          # Create order API
├── update_order_status.php   # Update status API
├── cancel_order.php          # Cancel order API
├── print_receipt.php         # Silent print API
└── success.php               # Success message
```

### Payment Files:
```
payment/
├── check_transaction.php     # 🔑 Payment verification (has API token)
└── index.php                 # Test page
```

### Admin Files:
```
public/admin/
├── dashboard.php             # Overview
├── products.php              # Product management
├── categories.php            # Category management
├── modifiers.php             # Size/topping management
├── orders.php                # Order history
├── reports.php               # Sales reports
├── settings.php              # System settings
├── test_printer.php          # Test printer
├── sidebar.php               # Navigation
└── login.html                # Login page
```

---

## 🔍 Quick Find Guide

### Need to update API token?
```bash
# Search for token
grep -n "token = " payment/check_transaction.php

# Output: Line 21
```

### Need to update merchant info?
```bash
# Search for merchant
grep -n "new Info" public/kiosk/product.php

# Output: Line ~355
```

### Need to find payment flow?
```bash
# Search for payment functions
grep -n "function.*payment" public/kiosk/product.php

# Functions:
# - processOrder()
# - generateKHQR()
# - checkPaymentStatus()
# - onPaymentSuccess()
```

---

## 📋 Update Checklist

When updating API credentials:

### Step 1: Update Payment Configuration
- [ ] Open `config/payment.php`
- [ ] Update `BAKONG_API_TOKEN` (line 11)
- [ ] Update `MERCHANT_ACCOUNT_ID` (line 14)
- [ ] Update `MERCHANT_NAME` (line 15)
- [ ] Update `MERCHANT_CITY` (line 16)
- [ ] Update `MERCHANT_MOBILE` (line 17)
- [ ] Save file

### Step 3: Test
- [ ] Open kiosk: `/public/kiosk/`
- [ ] Select a product
- [ ] Click "Order Now"
- [ ] Check QR code generates
- [ ] Make small test payment
- [ ] Verify payment detected
- [ ] Check order status updates

### Step 4: Verify
- [ ] Check admin dashboard
- [ ] Verify order appears
- [ ] Check payment status
- [ ] Test receipt printing

---

## 🆘 Troubleshooting

### "Payment not detected"
**Check these files:**
1. `payment/check_transaction.php` - Line 21 (API token)
2. Browser console (F12) - Check for errors
3. Network tab - Check API calls

**Common causes:**
- Expired token
- Wrong token
- Network issues
- API down

### "QR code not showing"
**Check these files:**
1. `public/kiosk/product.php` - Line ~355 (Merchant info)
2. Browser console - Check for library errors

**Common causes:**
- KHQR library not loaded
- Invalid merchant info
- JavaScript errors

### "Wrong business name on QR"
**Update here:**
1. `public/kiosk/product.php` - Line ~355
2. Change second parameter in `new Info()`

---

## 📊 File Comparison

### Before (Old System):
```
public/kiosk/
├── index.php
├── product.php
├── cart.php          ← REMOVED
├── checkout.php      ← REMOVED
├── payment.php       ← REMOVED
└── success.php
```

### After (Current System):
```
public/kiosk/
├── index.php
├── product.php       ← Now handles everything
├── create_order.php  ← NEW
├── cancel_order.php  ← NEW
├── print_receipt.php ← NEW
└── success.php
```

**Result:**
- 3 files removed
- 3 new API files added
- Simpler, more maintainable code
- Better user experience

---

## 📖 Documentation Map

### For Quick Updates:
→ `QUICK_UPDATE_GUIDE.md`

### For Payment Details:
→ `PAYMENT_API_GUIDE.md`

### For System Overview:
→ `SYSTEM_ARCHITECTURE.md`

### For Getting Started:
→ `README.md`

### For Changes History:
→ `CHANGELOG.md`

### For This Info:
→ `FILES_REMOVED_AND_LOCATIONS.md` (you are here)

---

## 🎯 Summary

### Removed: 3 files
- `cart.php` - No cart system
- `checkout.php` - Direct payment
- `payment.php` - Integrated into product.php

### API Locations: 2 places
1. **Token:** `payment/check_transaction.php` line 21
2. **Merchant:** `public/kiosk/product.php` line ~355

### Documentation: 7 files
- README.md
- PAYMENT_API_GUIDE.md
- QUICK_UPDATE_GUIDE.md
- SYSTEM_ARCHITECTURE.md
- CHANGELOG.md
- FILES_REMOVED_AND_LOCATIONS.md
- database/README.md

---

**Quick Access:**
- Need to update API? → `payment/check_transaction.php:21`
- Need to update merchant? → `public/kiosk/product.php:355`
- Need help? → `QUICK_UPDATE_GUIDE.md`

**Last Updated:** 2024  
**System Version:** 1.0
