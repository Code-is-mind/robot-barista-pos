# ⏱️ Countdown Timer Test Guide

## Quick Test

1. **Open Browser Console (F12)**
2. **Make an order**
3. **Watch for these logs:**

```
Starting payment countdown: 30 seconds
Countdown updated: 30 seconds remaining
Countdown updated: 29 seconds remaining
Countdown updated: 28 seconds remaining
...
Countdown updated: 1 seconds remaining
Countdown updated: 0 seconds remaining
Payment timeout reached!
```

4. **Watch the modal:**
   - Should show: `0:30`
   - Then: `0:29`
   - Then: `0:28`
   - ...
   - Finally: `0:00`

5. **At 0:00:**
   - Modal closes
   - "Payment timeout" message appears
   - Redirects to kiosk after 3 seconds

---

## If Countdown Not Updating

### Check 1: Element Exists
Open console and type:
```javascript
document.getElementById('paymentCountdown')
```

Should return: `<span id="paymentCountdown">...</span>`

If `null`, the element doesn't exist.

### Check 2: Console Errors
Look for:
```
Countdown element not found!
```

This means the modal HTML is missing the countdown element.

### Check 3: Interval Running
After clicking "Order Now", check console:
```
Starting payment countdown: 30 seconds
```

If you don't see this, the function isn't being called.

---

## Expected Behavior

### Visual (in modal):
```
Time remaining: 0:30
Time remaining: 0:29
Time remaining: 0:28
...
Time remaining: 0:01
Time remaining: 0:00
```

### Console logs:
```
Starting payment countdown: 30 seconds
Countdown updated: 30 seconds remaining
Payment check response: {responseCode: 1, ...}
Countdown updated: 29 seconds remaining
Payment check response: {responseCode: 1, ...}
...
Countdown updated: 0 seconds remaining
Payment timeout reached!
```

---

## Troubleshooting

### Issue: Countdown shows 0:30 but doesn't change

**Cause:** Interval not starting

**Fix:**
1. Check browser console for errors
2. Make sure `countdownInterval` is being set
3. Verify `setInterval` is working

**Test:**
```javascript
// In console, test setInterval
let test = 5;
const testInterval = setInterval(() => {
    console.log('Test:', test--);
    if (test <= 0) clearInterval(testInterval);
}, 1000);
```

### Issue: Countdown updates but modal doesn't close

**Cause:** `onPaymentTimeout()` not being called

**Check console for:**
```
Payment timeout reached!
```

If you see this but modal doesn't close, check the `onPaymentTimeout()` function.

### Issue: Countdown jumps or skips numbers

**Cause:** Multiple intervals running

**Fix:**
1. Clear old intervals before starting new ones
2. Make sure `cancelPayment()` clears intervals
3. Check for duplicate `setInterval` calls

---

## Manual Test

### Test 1: Full Countdown
1. Order product
2. Don't pay
3. Watch countdown: 30 → 29 → ... → 0
4. Verify modal closes at 0
5. Verify redirect to kiosk

### Test 2: Cancel Before Timeout
1. Order product
2. Wait 10 seconds (countdown shows 0:20)
3. Click "Cancel Payment"
4. Verify countdown stops
5. Verify modal closes immediately

### Test 3: Pay Before Timeout
1. Order product
2. Scan QR and pay
3. Verify modal closes before timeout
4. Verify countdown stops
5. Verify receipt modal appears

---

## Success Criteria

✅ Countdown starts at `0:30`
✅ Updates every second
✅ Shows in console logs
✅ Displays in modal
✅ Reaches `0:00` after 30 seconds
✅ Modal closes automatically
✅ Order cancelled
✅ Redirects to kiosk

---

## Debug Commands

### Check if countdown element exists:
```javascript
document.getElementById('paymentCountdown')
```

### Check if intervals are running:
```javascript
// After ordering, check these variables
console.log('Payment interval:', paymentCheckInterval);
console.log('Countdown interval:', countdownInterval);
```

### Manually trigger timeout:
```javascript
onPaymentTimeout();
```

### Check countdown value:
```javascript
document.getElementById('paymentCountdown').textContent
```

---

**Last Updated:** 2024  
**Timeout:** 30 seconds  
**Update Interval:** 1 second
