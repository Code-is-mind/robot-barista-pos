# Dynamic Exchange Rate System

## Overview
The system now uses the exchange rate from the `settings` table for all currency conversions in real-time.

## How It Works

### 1. Exchange Rate Storage
- Stored in database: `settings` table
- Key: `exchange_rate_usd_to_khr`
- Example value: `4100` (meaning 1 USD = 4,100 KHR)
- Admin can update this value anytime from the admin panel

### 2. Price Calculation Flow

#### On Page Load (PHP):
```php
// Fetch exchange rate from database
$exchangeRate = $settings['exchange_rate_usd_to_khr'] ?? 4100;

// Calculate KHR prices dynamically
$priceKHR = $priceUSD * $exchangeRate;
```

#### In JavaScript:
```javascript
// Exchange rate passed from PHP
const exchangeRate = 4100; // From database

// Calculate prices dynamically
const khrPrice = usdPrice * exchangeRate;
```

### 3. Where Exchange Rate is Used

**Product Page (`product.php`):**
- Base product price conversion (USD → KHR)
- Size modifier price conversion (USD → KHR)
- Order summary calculations
- Payment amount display

**Index Page (`index.php`):**
- Product grid price display
- Currency toggle calculations

**Order Creation (`create_order.php`):**
- Converting KHR payments back to USD for storage
- Recording exchange rate used in notes

### 4. Example Scenarios

**Scenario 1: Admin Updates Exchange Rate**
1. Admin changes rate from 4,100 to 4,150 in settings
2. Customer visits kiosk
3. System fetches new rate: 4,150
4. Coffee $2.50 now shows as ៛10,375 (instead of ៛10,250)
5. All calculations use the new rate immediately

**Scenario 2: Customer Orders in KHR**
1. Product: $2.50
2. Exchange rate: 4,100
3. Customer sees: ៛10,250
4. Customer pays: ៛10,250
5. System stores: $2.50 USD
6. Notes: "Customer paid in KHR: ៛10,250 (Exchange rate: 4,100)"

## Benefits

✅ **Real-time Updates**: Exchange rate changes apply immediately
✅ **No Manual Updates**: Prices auto-calculate from USD base
✅ **Consistent**: Single source of truth (settings table)
✅ **Audit Trail**: Exchange rate recorded in order notes
✅ **Admin Control**: Easy to update from admin panel

## Files Modified

- `public/kiosk/product.php` - Uses dynamic exchange rate for all calculations
- `public/kiosk/index.php` - Already using dynamic rate
- `public/kiosk/create_order.php` - Already using dynamic rate

## Important Notes

1. **Base Currency**: All products stored in USD in database
2. **KHR Prices**: Always calculated on-the-fly using exchange rate
3. **No Cache**: Exchange rate fetched fresh on every page load
4. **Order Storage**: Always in USD with exchange rate in notes

## Testing

To test the system:
1. Update exchange rate in settings table
2. Refresh kiosk page
3. Verify prices update automatically
4. Place order in KHR
5. Check order notes contain correct exchange rate
