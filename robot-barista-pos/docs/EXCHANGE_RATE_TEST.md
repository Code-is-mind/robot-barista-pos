# Exchange Rate Testing Guide

## Test Scenario

### Setup
- Product: Brownie
- Base Price: $2.00 USD
- Exchange Rate: 100 (1 USD = 100 KHR)

### Expected Results

#### Index Page (Product Grid)
- **USD Mode**: $2.00
- **KHR Mode**: ៛200 (not ៛8,200)

#### Product Page
- **Base Price USD**: $2.00
- **Base Price KHR**: ៛200
- **Size Modifier (Small +$0.50) USD**: $0.50
- **Size Modifier (Small +$0.50) KHR**: ៛50

#### Order Summary
- **Subtotal USD**: $2.00
- **Subtotal KHR**: ៛200
- **Tax (10%) USD**: $0.20
- **Tax (10%) KHR**: ៛20
- **Total USD**: $2.20
- **Total KHR**: ៛220

#### Payment Modal
- **Amount USD**: $2.20
- **Amount KHR**: ៛220

#### Database Storage
- **Currency**: USD
- **Total**: 2.20
- **Notes**: "Customer paid in KHR: ៛220 (Exchange rate: 100)"

## How to Test

1. **Update Exchange Rate**
   ```sql
   UPDATE settings 
   SET setting_value = '100' 
   WHERE setting_key = 'exchange_rate_usd_to_khr';
   ```

2. **Test Index Page**
   - Visit kiosk index
   - Toggle to KHR
   - Verify Brownie shows ៛200 (not ៛8,200)

3. **Test Product Page**
   - Click on Brownie
   - Verify base price shows ៛200
   - Verify size modifiers calculate correctly
   - Verify order summary shows ៛220 total

4. **Test Payment**
   - Click "Order Now"
   - Verify payment modal shows ៛220
   - Complete payment
   - Check database order notes

5. **Test Different Exchange Rates**
   - Try rate: 4,100 → Brownie should show ៛8,200
   - Try rate: 4,000 → Brownie should show ៛8,000
   - Try rate: 100 → Brownie should show ៛200

## Fixed Issues

✅ Product grid prices now use dynamic exchange rate
✅ Product page base price uses dynamic exchange rate
✅ Size modifiers use dynamic exchange rate
✅ Order summary calculates with dynamic exchange rate
✅ Payment modal shows correct amount
✅ Database stores in USD with exchange rate in notes

## Formula

```
KHR Price = USD Price × Exchange Rate
```

Examples:
- $2.00 × 100 = ៛200
- $2.00 × 4,100 = ៛8,200
- $0.50 × 100 = ៛50
- $0.50 × 4,100 = ៛2,050
