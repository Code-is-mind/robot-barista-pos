# 🐛 Payment Debugging Guide

## Quick Debug Steps

### 1. Check PHP Error Logs

**Linux/Mac:**
```bash
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/php/error.log
```

**Windows (XAMPP):**
```
C:\xampp\apache\logs\error.log
```

**Look for:**
```
=== Check Transaction Request ===
Received MD5: abc123...
API URL: https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5
Using token: eyJhbGciOiJIUzI1NiIs...
HTTP Code: 200
Response: {"responseCode":0,"data":{...}}
Response Code: 0
```

---

### 2. Check Browser Console

**Open DevTools (F12) → Console Tab**

**Look for:**
```javascript
// Every second you should see:
Payment check response: {responseCode: 1, responseMessage: "Transaction not found"}

// When payment successful:
Payment check response: {responseCode: 0, data: {...}}
```

---

### 3. Check Network Tab

**Open DevTools (F12) → Network Tab → Filter: XHR**

**Find:** `check_transaction.php` requests

**Check:**
- Status: Should be `200 OK`
- Response: Should show JSON
- Headers: Should have `Authorization: Bearer ...`

---

## Common Issues

### Issue 1: "Payment not detected even after paying"

**Possible Causes:**

1. **Wrong MD5 Hash**
   - Check console: Look for the MD5 value
   - Should be 32 characters long
   - Example: `a1b2c3d4e5f6...`

2. **API Token Expired**
   ```bash
   # Check token expiration
   # Go to https://jwt.io
   # Paste your token
   # Look at "exp" field
   ```

3. **Wrong API URL**
   ```bash
   # Should be:
   https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5
   
   # NOT:
   https://api-bakong.nbc.gov.kh/check_transaction_by_md5
   ```

4. **Network/Firewall Issue**
   ```bash
   # Test from server
   curl -X POST https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5 \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"md5":"test"}'
   ```

---

### Issue 2: "responseCode always 1"

**This is NORMAL if no payment made yet!**

```json
{
  "responseCode": 1,
  "responseMessage": "Transaction not found"
}
```

**This means:**
- API is working ✅
- No payment received yet ⏳
- Keep checking...

**When payment successful:**
```json
{
  "responseCode": 0,
  "data": {
    "amount": 5000,
    "currency": "KHR",
    ...
  }
}
```

---

### Issue 3: "Modal closes immediately"

**Check:**
1. Countdown timer - should show `0:30`
2. Console for errors
3. `maxChecks` value - should be `30`

**Fix:**
```javascript
// In product.php, line ~560
const maxChecks = 30; // Should be 30, not 1800
```

---

### Issue 4: "QR code not showing"

**Check:**
1. KHQR library loaded
   ```javascript
   console.log(typeof BakongKHQR);
   // Should output: "object"
   ```

2. Merchant info correct
   ```bash
   # Check config/payment.php
   MERCHANT_ACCOUNT_ID = 'your_account@bank'
   MERCHANT_NAME = 'Your Name'
   ```

3. Browser console for errors

---

## Testing Payment Detection

### Test 1: Check API Connection

```bash
# From your server
curl -X POST https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"md5":"test_hash_12345"}'
```

**Expected Response:**
```json
{
  "responseCode": 1,
  "responseMessage": "Transaction not found",
  "data": null
}
```

**If you get this, API is working! ✅**

---

### Test 2: Make Real Payment

1. **Generate QR code**
   - Order a product
   - Note the MD5 hash in console

2. **Scan and pay**
   - Use banking app
   - Pay the exact amount

3. **Watch console**
   - Should see `responseCode: 0` within 5 seconds
   - Modal should close automatically

4. **Check database**
   ```sql
   SELECT * FROM orders ORDER BY created_at DESC LIMIT 1;
   -- payment_status should be 'Paid'
   ```

---

### Test 3: Check Timeout

1. **Generate QR code**
2. **Don't pay**
3. **Wait 30 seconds**
4. **Should see:**
   - Countdown reaches `0:00`
   - "Payment timeout" message
   - Redirect to kiosk

---

## Debug Checklist

When payment not working:

- [ ] Check `config/payment.php` exists
- [ ] Check API token is correct
- [ ] Check merchant info is correct
- [ ] Check PHP error logs
- [ ] Check browser console
- [ ] Check network tab (XHR requests)
- [ ] Test API with curl
- [ ] Verify QR code generates
- [ ] Verify countdown timer works
- [ ] Check database for order creation

---

## Log Analysis

### Good Logs (Working):
```
=== Check Transaction Request ===
Received MD5: a1b2c3d4e5f6...
API URL: https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5
Using token: eyJhbGciOiJIUzI1NiIs...
HTTP Code: 200
Response: {"responseCode":1,"responseMessage":"Transaction not found"}
Response Code: 1
```

### Bad Logs (Not Working):
```
=== Check Transaction Request ===
Received MD5: a1b2c3d4e5f6...
API URL: https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5
Using token: eyJhbGciOiJIUzI1NiIs...
HTTP Code: 401
Response: {"error":"Unauthorized"}
CURL Error: 
```

**401 = Token expired or invalid**

---

## Quick Fixes

### Fix 1: Update Token
```bash
nano config/payment.php
# Update line 11
define('BAKONG_API_TOKEN', 'YOUR_NEW_TOKEN');
```

### Fix 2: Check Timeout
```bash
nano public/kiosk/product.php
# Find line ~560
const maxChecks = 30; // Should be 30 seconds
```

### Fix 3: Clear Browser Cache
```
Ctrl + Shift + Delete
Clear cache and reload
```

### Fix 4: Restart Apache
```bash
sudo systemctl restart apache2
# or
sudo service apache2 restart
```

---

## Success Indicators

Payment system is working when you see:

✅ **In PHP Logs:**
```
HTTP Code: 200
Response Code: 1 (before payment)
Response Code: 0 (after payment)
```

✅ **In Browser Console:**
```
Payment check response: {responseCode: 1, ...}
Payment check response: {responseCode: 0, data: {...}}
```

✅ **In Network Tab:**
```
check_transaction.php: 200 OK
Response: {"responseCode":0,"data":{...}}
```

✅ **In Database:**
```sql
payment_status = 'Paid'
order_status = 'Preparing'
```

---

## Still Not Working?

1. **Check config file loaded:**
   ```php
   // Add to check_transaction.php temporarily
   error_log("Config loaded: " . (function_exists('getBakongConfig') ? 'YES' : 'NO'));
   ```

2. **Check token format:**
   - Should start with `eyJ`
   - Should have 3 parts separated by `.`
   - Example: `eyJhbGc...`

3. **Check API endpoint:**
   - Must be exact URL
   - Must include `/v1/`
   - Must be HTTPS

4. **Contact support:**
   - NBC Bakong: support@nbc.gov.kh
   - Phone: +855 23 001 104

---

**Last Updated:** 2024  
**Timeout:** 30 seconds  
**Check Interval:** 1 second
