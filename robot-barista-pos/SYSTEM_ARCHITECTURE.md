# 🏗️ System Architecture

## Payment Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         CUSTOMER FLOW                            │
└─────────────────────────────────────────────────────────────────┘

1. Browse Products
   ↓
   [index.php] - Display all products
   
2. Select Product
   ↓
   [product.php] - Choose size, quantity, enter name
   
3. Click "Order Now"
   ↓
   [product.php - processOrder()] 
   ├─ Calculate total (base + size + tax)
   ├─ Generate KHQR code
   └─ Show payment modal
   
4. Scan QR Code
   ↓
   [generateKHQR()] - Uses Bakong KHQR Library
   ├─ Merchant: seavpeav_pech@aclb  ← UPDATE HERE
   ├─ Amount: Calculated total
   └─ MD5 Hash: For tracking
   
5. Create Order
   ↓
   [create_order.php] - AJAX Call
   ├─ Insert into orders table
   ├─ Insert into order_items table
   └─ Return order_id
   
6. Check Payment Status (Every 1 second)
   ↓
   [check_transaction.php] - AJAX Polling
   ├─ API: https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5
   ├─ Token: Bearer eyJhbGc...  ← UPDATE HERE
   ├─ Send: MD5 hash
   └─ Receive: Payment status
   
7. Payment Confirmed
   ↓
   [onPaymentSuccess()]
   ├─ Update order status to "Paid"
   ├─ Hide payment modal
   └─ Show receipt modal
   
8. Print Receipt? (10 sec timeout)
   ↓
   YES → [print_receipt.php] - Silent print to thermal printer
   NO  → Skip to next step
   
9. Preparing Order (10 sec)
   ↓
   [showPreparingModal()] - Robot animation
   
10. Complete!
    ↓
    [showSuccessAndRedirect()] - Toast message → Back to index.php
```

---

## File Structure & Responsibilities

```
📦 Robot Barista POS
│
├── 📁 config/
│   └── database.php              # Database connection
│
├── 📁 database/
│   ├── schema.sql                # Full database schema
│   ├── add_has_modifiers.sql    # Migration: Product modifiers
│   └── add_printer_settings.sql # Migration: Printer config
│
├── 📁 payment/                   # 🔑 PAYMENT API HERE
│   ├── check_transaction.php    # ← API TOKEN (Line 21)
│   └── index.php                 # Test page
│
├── 📁 public/
│   │
│   ├── 📁 kiosk/                 # Customer-facing
│   │   ├── index.php             # Product listing
│   │   ├── product.php           # ← MERCHANT INFO (Line 355)
│   │   ├── create_order.php      # Create order API
│   │   ├── update_order_status.php # Update status API
│   │   ├── cancel_order.php      # Cancel order API
│   │   └── print_receipt.php     # Silent print API
│   │
│   └── 📁 admin/                 # Admin panel
│       ├── login.html            # Admin login
│       ├── dashboard.php         # Overview & stats
│       ├── products.php          # Product management
│       ├── categories.php        # Category management
│       ├── modifiers.php         # Size/topping management
│       ├── orders.php            # Order history
│       ├── reports.php           # Sales reports
│       ├── settings.php          # System settings
│       ├── test_printer.php      # Test printer connection
│       └── sidebar.php           # Navigation menu
│
├── print-receipt.php             # Browser print fallback
│
├── PAYMENT_API_GUIDE.md          # 📖 Detailed API docs
├── QUICK_UPDATE_GUIDE.md         # ⚡ Quick reference
└── SYSTEM_ARCHITECTURE.md        # 🏗️ This file
```

---

## Database Schema

```
┌─────────────┐
│  settings   │ ← Business info, API keys, printer config
└─────────────┘

┌─────────────┐
│ categories  │ ← Coffee, Tea, Drinks, etc.
└─────────────┘
       │
       │ 1:N
       ↓
┌─────────────┐
│  products   │ ← Latte, Cappuccino, etc.
│             │   has_modifiers: 0/1
└─────────────┘
       │
       │ N:M
       ↓
┌─────────────┐
│  modifiers  │ ← Small, Medium, Large
└─────────────┘

┌─────────────┐
│   orders    │ ← Order header (total, status, etc.)
└─────────────┘
       │
       │ 1:N
       ↓
┌─────────────┐
│ order_items │ ← Order details (products, qty, etc.)
└─────────────┘

┌─────────────┐
│ print_logs  │ ← Print success/failure tracking
└─────────────┘
```

---

## API Integration Points

### 1. Bakong KHQR Generation
**Library:** `bakong-khqr-1.0.6.min.js`
**Used in:** `public/kiosk/product.php`
**Purpose:** Generate QR codes for payment

```javascript
const KHQR = BakongKHQR;
const individualInfo = new KHQR.IndividualInfo(
    "merchant_account@bank",  // ← Your account
    "Merchant Name",          // ← Your name
    "City",                   // ← Your city
    optionalData
);
```

### 2. Bakong Payment Check
**API:** `https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5`
**Used in:** `payment/check_transaction.php`
**Purpose:** Verify payment received

```php
$url = 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5';
$token = 'Bearer YOUR_TOKEN';  // ← Your token
```

### 3. QR Code Display
**Library:** `qrcode.min.js`
**Used in:** `public/kiosk/product.php`
**Purpose:** Display QR code on screen

```javascript
QRCode.toCanvas(canvas, qrString, { width: 300 });
```

---

## Security Considerations

### 🔒 What's Protected:
- Admin panel requires login
- API tokens in server-side PHP (not exposed to client)
- Database credentials in config file
- HTTPS recommended for production

### ⚠️ What to Secure:
1. **API Token** - Keep secret, rotate regularly
2. **Database Password** - Use strong password
3. **Admin Password** - Change default password
4. **File Permissions** - Restrict config files

### 🛡️ Best Practices:
```bash
# Secure config file
chmod 600 config/database.php

# Secure payment check
chmod 600 payment/check_transaction.php

# Restrict uploads directory
chmod 755 public/uploads/
```

---

## Performance Optimization

### Current Setup:
- Payment check: Every 1 second for 300 seconds (5 minutes)
- Database: MySQL with indexes on frequently queried fields
- Images: Stored locally in `public/uploads/`

### Recommendations:
1. **Enable caching** for product images
2. **Use CDN** for static assets in production
3. **Optimize images** before upload (max 800x800px)
4. **Add database indexes** on order_number, created_at
5. **Monitor API rate limits** from Bakong

---

## Deployment Checklist

### Before Going Live:

- [ ] Update API token in `payment/check_transaction.php`
- [ ] Update merchant info in `public/kiosk/product.php`
- [ ] Configure printer in Admin → Settings
- [ ] Test printer connection
- [ ] Update business info (name, address, phone)
- [ ] Change admin password
- [ ] Enable HTTPS
- [ ] Test full payment flow
- [ ] Test receipt printing
- [ ] Set up database backups
- [ ] Monitor error logs

---

## Monitoring & Maintenance

### Daily Checks:
```sql
-- Check today's orders
SELECT COUNT(*), SUM(total_amount) 
FROM orders 
WHERE DATE(created_at) = CURDATE();

-- Check payment failures
SELECT COUNT(*) 
FROM orders 
WHERE payment_status = 'Failed' 
AND DATE(created_at) = CURDATE();

-- Check print failures
SELECT COUNT(*) 
FROM print_logs 
WHERE print_status = 'failed' 
AND DATE(printed_at) = CURDATE();
```

### Weekly Tasks:
- Review sales reports
- Check printer paper level
- Verify API token expiration
- Backup database
- Clear old print logs

### Monthly Tasks:
- Update exchange rate if needed
- Review and update product prices
- Check system performance
- Update software dependencies

---

## Troubleshooting Guide

### Issue: Payment not detected
**Check:**
1. API token valid? → `payment/check_transaction.php`
2. Network connection? → Can reach api-bakong.nbc.gov.kh
3. MD5 hash correct? → Check browser console

### Issue: QR code not showing
**Check:**
1. KHQR library loaded? → Check browser console
2. Merchant info correct? → `public/kiosk/product.php`
3. Amount valid? → Must be positive number

### Issue: Printer not working
**Check:**
1. Printer enabled? → Admin → Settings
2. IP address correct? → Test printer connection
3. Network accessible? → Ping printer IP
4. Paper loaded? → Check printer

### Issue: Wrong prices
**Check:**
1. Exchange rate → Admin → Settings
2. Product prices → Admin → Products
3. Modifier prices → Admin → Modifiers
4. Tax percentage → Admin → Settings

---

## Support Resources

### Documentation:
- `PAYMENT_API_GUIDE.md` - Detailed payment API docs
- `QUICK_UPDATE_GUIDE.md` - Quick reference for updates
- `database/README.md` - Database migration guide

### External Resources:
- Bakong API: https://bakong.nbc.gov.kh
- KHQR Library: https://github.com/davidhuotkeo/bakong-khqr
- QRCode.js: https://github.com/soldair/node-qrcode

### Contact:
- NBC Bakong Support: support@nbc.gov.kh
- Phone: +855 23 001 104

---

**System Version:** 1.0  
**Last Updated:** 2024  
**Architecture:** PHP + MySQL + JavaScript  
**Payment:** Bakong KHQR (NBC Cambodia)
