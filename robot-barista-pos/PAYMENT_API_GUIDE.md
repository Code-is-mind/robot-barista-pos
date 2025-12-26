# Payment API Configuration Guide

## Overview
This system uses **KHQR (Bakong QR)** payment system from National Bank of Cambodia for processing payments.

---

## 🔑 Where to Update API Keys

### 1. **Payment Check API - Token Location**
**File:** `payment/check_transaction.php`

```php
// Line 20-21
$url = 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5';
$token = 'YOUR_API_TOKEN_HERE';
```

**Current Token:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImlkIjoiZDAyZmIxNTc3YjgxNDEyYiJ9LCJpYXQiOjE3NjMxODk4NDUsImV4cCI6MTc3MDk2NTg0NX0.hYrGbKIzrNy7X6fCdWmiofhkE8dX9IMi5pJRgynNMvc
```

**How to Update:**
1. Open `payment/check_transaction.php`
2. Find line with `$token = '...'`
3. Replace with your new token
4. Save file

---

### 2. **KHQR Generation - Merchant Info**
**File:** `public/kiosk/product.php`

```javascript
// Lines 350-360 (approximately)
const individualInfo = new Info(
    "seavpeav_pech@aclb",      // ← Merchant Account ID
    "SEAVPEAV PECH",            // ← Merchant Name
    "PHNOM PENH",               // ← Merchant City
    optionalData
);
```

**How to Update:**
1. Open `public/kiosk/product.php`
2. Search for `new Info(`
3. Update the three parameters:
   - **Account ID**: Your Bakong account (e.g., `yourname@bankcode`)
   - **Merchant Name**: Your business name
   - **City**: Your business location
4. Save file

---

## 📍 Payment Flow in System

### Step-by-Step Process:

1. **Customer Orders** (`public/kiosk/product.php`)
   - Customer selects product, size, quantity
   - Clicks "Order Now"
   - JavaScript function `processOrder()` is called

2. **KHQR Generation** (`public/kiosk/product.php` - Line ~340)
   ```javascript
   function generateKHQR(amount, selectedCurrency, description, ...)
   ```
   - Uses Bakong KHQR library
   - Generates QR code with payment details
   - Creates MD5 hash for transaction tracking

3. **Order Creation** (`public/kiosk/create_order.php`)
   - Creates order in database
   - Status: "Pending"
   - Stores order details

4. **Payment Checking** (`payment/check_transaction.php`)
   - Polls Bakong API every 1 second
   - Checks if payment received using MD5 hash
   - API Endpoint: `https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5`
   - Uses Bearer token for authentication

5. **Payment Success** (`public/kiosk/product.php` - Line ~400)
   ```javascript
   function onPaymentSuccess()
   ```
   - Updates order status to "Paid"
   - Shows receipt modal
   - Offers to print receipt

6. **Receipt Printing** (`public/kiosk/print_receipt.php`)
   - Silent print to thermal printer
   - Or fallback to browser print

---

## 🔧 Configuration Files

### Main Configuration Files:

| File | Purpose | What to Update |
|------|---------|----------------|
| `payment/check_transaction.php` | Payment verification | API Token |
| `public/kiosk/product.php` | KHQR generation | Merchant info |
| `public/admin/settings.php` | Business settings | Business name, address, phone |

---

## 🔐 API Token Management

### Getting a New Token:

1. **Register with NBC Bakong**
   - Visit: https://bakong.nbc.gov.kh
   - Register as merchant
   - Get API credentials

2. **Token Format:**
   ```
   JWT Token (JSON Web Token)
   Format: eyJhbGc...
   ```

3. **Token Expiration:**
   - Check `exp` field in token
   - Current token expires: **2026-04-13**
   - Update before expiration to avoid service interruption

### Decoding Token (for checking expiration):
```bash
# Use online tool: https://jwt.io
# Or command line:
echo "YOUR_TOKEN" | cut -d'.' -f2 | base64 -d
```

---

## 📝 Testing Payment System

### Test Mode:
The system currently uses **PRODUCTION** Bakong API.

### To Test:
1. Use small amounts (e.g., 1000 KHR)
2. Scan QR with real banking app
3. Check transaction appears in system
4. Verify order status updates

### Test Checklist:
- [ ] QR code generates correctly
- [ ] Payment amount matches order total
- [ ] Transaction detected within 5 seconds
- [ ] Order status updates to "Paid"
- [ ] Receipt prints/displays correctly

---

## 🚨 Troubleshooting

### Payment Not Detected:
1. **Check API Token**
   - Verify token is valid
   - Check expiration date
   - Test with curl:
   ```bash
   curl -X POST https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5 \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"md5":"test_hash"}'
   ```

2. **Check Network Connection**
   - Server must reach `api-bakong.nbc.gov.kh`
   - Port 443 (HTTPS) must be open

3. **Check Browser Console**
   - Open Developer Tools (F12)
   - Look for errors in Console tab
   - Check Network tab for API calls

### QR Code Not Generating:
1. Check KHQR library loaded:
   ```javascript
   console.log(typeof BakongKHQR);
   // Should output: "object"
   ```

2. Verify merchant info is correct

3. Check amount is valid number

---

## 📚 External Libraries Used

### 1. Bakong KHQR Library
- **CDN:** `https://github.com/davidhuotkeo/bakong-khqr/releases/download/bakong-khqr-1.0.6/khqr-1.0.6.min.js`
- **Purpose:** Generate KHQR codes
- **Documentation:** https://github.com/davidhuotkeo/bakong-khqr

### 2. QRCode.js
- **CDN:** `https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js`
- **Purpose:** Display QR codes on screen
- **Documentation:** https://github.com/soldair/node-qrcode

---

## 🔄 Updating to New API Version

If NBC releases new API version:

1. **Update API URL** in `payment/check_transaction.php`:
   ```php
   $url = 'https://api-bakong.nbc.gov.kh/v2/check_transaction_by_md5'; // v2
   ```

2. **Update KHQR Library** in `public/kiosk/product.php`:
   ```html
   <script src="https://github.com/.../khqr-2.0.0.min.js"></script>
   ```

3. **Test thoroughly** before deploying to production

---

## 📞 Support

### NBC Bakong Support:
- Website: https://bakong.nbc.gov.kh
- Email: support@nbc.gov.kh
- Phone: +855 23 001 104

### System Issues:
- Check logs in `print_logs` table
- Review browser console errors
- Test API connection manually

---

## ⚠️ Security Notes

1. **Never commit API tokens to Git**
   - Use environment variables in production
   - Keep tokens in secure configuration

2. **HTTPS Required**
   - Always use HTTPS in production
   - Protects payment data in transit

3. **Token Rotation**
   - Rotate tokens periodically
   - Update before expiration
   - Keep backup of old token during transition

---

## 📊 Monitoring

### Check Payment Success Rate:
```sql
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total_orders,
    SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) as paid_orders,
    ROUND(SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as success_rate
FROM orders
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

### Check Print Success Rate:
```sql
SELECT 
    print_status,
    COUNT(*) as count
FROM print_logs
WHERE printed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY print_status;
```

---

## 🎯 Quick Reference

| What | Where | Line |
|------|-------|------|
| API Token | `payment/check_transaction.php` | ~21 |
| Merchant Account | `public/kiosk/product.php` | ~355 |
| API URL | `payment/check_transaction.php` | ~20 |
| Payment Polling | `public/kiosk/product.php` | ~410 |
| Order Creation | `public/kiosk/create_order.php` | ~25 |
| Receipt Print | `public/kiosk/print_receipt.php` | ~15 |

---

**Last Updated:** 2024
**System Version:** 1.0
**API Version:** Bakong v1
