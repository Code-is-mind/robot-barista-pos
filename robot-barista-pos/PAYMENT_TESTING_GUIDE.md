# 🧪 Payment Testing Guide

## Testing Payment Detection

### Quick Test Checklist:

1. **Open Browser Console (F12)**
   - Go to Console tab
   - Watch for payment check logs

2. **Make Test Order**
   - Select a product
   - Click "Order Now"
   - QR code should appear

3. **Check Console Logs**
   You should see:
   ```
   Payment check response: {responseCode: 1, responseMessage: "..."}
   ```
   
   When payment received:
   ```
   Payment check response: {responseCode: 0, data: {...}}
   ```

4. **Verify Countdown**
   - Timer should show: `30:00`
   - Should count down: `29:59`, `29:58`, etc.
   - Updates every second

---

## Payment Modal Features

### ✅ What Should Happen:

1. **QR Code Display:**
   - QR code appears immediately
   - Shows correct amount
   - Shows merchant name

2. **Countdown Timer:**
   - Starts at 30:00 (30 minutes)
   - Counts down every second
   - Shows format: MM:SS

3. **Payment Detection:**
   - Checks every 1 second
   - Continues for 30 minutes (1800 checks)
   - Stops when payment detected

4. **Timeout Behavior:**
   - After 30 minutes without payment:
     - Order cancelled automatically
     - Modal closes
     - Shows "Payment timeout" message
     - Redirects to kiosk page after 3 seconds

5. **Cancel Button:**
   - Stops payment checking
   - Cancels order
   - Closes modal immediately
   - Returns to product page

---

## Testing Scenarios

### Scenario 1: Successful Payment
```
1. Customer orders product
2. QR code appears
3. Customer scans and pays
4. Within 5 seconds:
   - Payment detected
   - Modal closes
   - Receipt modal appears
5. Order status = "Paid"
```

### Scenario 2: Payment Timeout
```
1. Customer orders product
2. QR code appears
3. Customer doesn't pay
4. After 30 minutes:
   - Countdown reaches 0:00
   - "Payment timeout" message
   - Order cancelled
   - Redirect to kiosk
5. Order status = "Cancelled"
```

### Scenario 3: Customer Cancels
```
1. Customer orders product
2. QR code appears
3. Customer clicks "Cancel Payment"
4. Immediately:
   - Modal closes
   - Order cancelled
   - Returns to product page
5. Order status = "Cancelled"
```

---

## Debugging Payment Issues

### Issue: Payment Not Detected

**Check 1: API Token**
```bash
# Open config/payment.php
nano config/payment.php

# Verify token is correct (line 11)
define('BAKONG_API_TOKEN', 'your_token_here');
```

**Check 2: Browser Console**
```javascript
// Look for errors in console
// Should see: "Payment check response: ..."
// If error: Check network tab for failed requests
```

**Check 3: Test API Directly**
```bash
curl -X POST https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"md5":"test_hash"}'
```

**Expected Response:**
```json
{
  "responseCode": 1,
  "responseMessage": "Transaction not found"
}
```

**Check 4: Network Tab**
1. Open DevTools (F12)
2. Go to Network tab
3. Filter: XHR
4. Look for `check_transaction.php` requests
5. Check response status (should be 200)
6. Check response body

---

## Console Logs to Watch

### Normal Operation:
```
Payment check response: {responseCode: 1, responseMessage: "Transaction not found", data: null}
Payment check response: {responseCode: 1, responseMessage: "Transaction not found", data: null}
...
Payment check response: {responseCode: 0, data: {amount: 5000, currency: "KHR", ...}}
```

### Error Conditions:
```
Payment check error: Failed to fetch
// Network issue or API down

Payment check response: {responseCode: 1, responseMessage: "Invalid token"}
// API token expired or incorrect

Payment check response: {httpCode: 401}
// Authentication failed
```

---

## Manual Testing Steps

### Test 1: QR Code Generation
```
1. Open kiosk
2. Select product
3. Click "Order Now"
4. Verify:
   ✓ QR code appears
   ✓ Amount is correct
   ✓ Countdown shows 30:00
   ✓ Description is correct
```

### Test 2: Payment Detection
```
1. Generate QR code
2. Scan with banking app
3. Complete payment
4. Within 5 seconds:
   ✓ Modal closes
   ✓ Receipt modal appears
   ✓ Order status updates
```

### Test 3: Countdown Timer
```
1. Generate QR code
2. Watch countdown
3. Verify:
   ✓ Starts at 30:00
   ✓ Counts down every second
   ✓ Format is MM:SS
   ✓ Reaches 0:00 after 30 minutes
```

### Test 4: Timeout Handling
```
1. Generate QR code
2. Wait 30 minutes (or modify code for faster test)
3. Verify:
   ✓ Modal closes automatically
   ✓ "Payment timeout" message appears
   ✓ Redirects to kiosk
   ✓ Order cancelled in database
```

### Test 5: Cancel Button
```
1. Generate QR code
2. Click "Cancel Payment"
3. Verify:
   ✓ Modal closes immediately
   ✓ Payment checking stops
   ✓ Order cancelled in database
   ✓ Returns to product page
```

---

## Quick Test (Fast Timeout)

For testing, you can temporarily reduce timeout:

**Edit `public/kiosk/product.php` (line ~550):**
```javascript
// Change from:
const maxChecks = 1800; // 30 minutes

// To:
const maxChecks = 60; // 1 minute (for testing)
```

**Remember to change back after testing!**

---

## Database Verification

### Check Order Status:
```sql
-- View recent orders
SELECT 
    order_number,
    customer_name,
    total_amount,
    payment_status,
    order_status,
    created_at
FROM orders
ORDER BY created_at DESC
LIMIT 10;
```

### Check Cancelled Orders:
```sql
-- View cancelled orders
SELECT 
    order_number,
    customer_name,
    payment_status,
    order_status,
    created_at
FROM orders
WHERE order_status = 'Cancelled'
ORDER BY created_at DESC;
```

---

## Common Issues & Solutions

### Issue: "Payment check response: undefined"
**Solution:** Check network tab, API might be down or blocked

### Issue: Countdown not updating
**Solution:** Check browser console for JavaScript errors

### Issue: Modal doesn't close after payment
**Solution:** Check `responseCode === 0` condition in code

### Issue: Timeout doesn't work
**Solution:** Verify `maxChecks` value is 1800

### Issue: Order not cancelled on timeout
**Solution:** Check `cancel_order.php` is working

---

## Performance Notes

### API Call Frequency:
- **Interval:** 1 second
- **Duration:** 30 minutes
- **Total Calls:** 1800 requests per order
- **Bandwidth:** ~1KB per request = ~1.8MB per order

### Recommendations:
- Monitor API rate limits
- Consider increasing interval to 2-3 seconds if needed
- Reduce timeout if 30 minutes is too long

---

## Success Criteria

Payment system is working correctly when:

- ✅ QR code generates instantly
- ✅ Payment detected within 5 seconds
- ✅ Countdown timer updates smoothly
- ✅ Timeout works after 30 minutes
- ✅ Cancel button works immediately
- ✅ Orders update correctly in database
- ✅ No JavaScript errors in console
- ✅ No failed API requests

---

**Need Help?**
- Check browser console for errors
- Check network tab for failed requests
- Verify API token in `config/payment.php`
- Test API connection manually with curl

**Last Updated:** 2024  
**Timeout Duration:** 30 minutes  
**Check Interval:** 1 second
