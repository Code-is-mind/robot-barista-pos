# Currency Handling System

## Overview
The system now stores all orders in USD regardless of the payment currency, with detailed notes about the actual payment.

## How It Works

### 1. Order Creation
When a customer places an order:
- If customer selects **USD**: Prices stored as-is in USD
- If customer selects **KHR**: Prices are converted to USD using the exchange rate before storage

### 2. Database Storage
All orders are stored in the database with:
- **Currency**: Always "USD"
- **Amounts**: Always in USD (subtotal, tax, total, unit_price)
- **Notes**: Contains payment details including:
  - Original currency customer paid in
  - Original amount in that currency
  - Exchange rate used (if KHR)
  - MD5 hash for transaction tracking
  - Transaction ID from bank (when payment confirmed)
  - Bank name
  - Payment timestamp

### 3. Payment Notes Format

**For USD payments:**
```
Customer paid in USD: $5.50 | MD5: abc123...
Transaction ID: TXN123456 | Bank: ABA Bank | Time: 2025-11-24 10:30:00
```

**For KHR payments:**
```
Customer paid in KHR: ៛22,550 (Exchange rate: 4,100) | MD5: abc123...
Transaction ID: TXN123456 | Bank: ABA Bank | Time: 2025-11-24 10:30:00
```

## Benefits

1. **Consistent Reporting**: All financial reports are in USD
2. **Audit Trail**: Full payment details preserved in notes
3. **Exchange Rate Tracking**: Know exactly what rate was used
4. **Transaction Traceability**: Bank transaction ID stored for reconciliation
5. **Multi-Currency Support**: Customers can pay in their preferred currency

## Files Modified

- `public/kiosk/create_order.php` - Converts KHR to USD, adds payment notes
- `public/kiosk/update_order_status.php` - Adds transaction ID to notes
- `public/kiosk/product.php` - Passes transaction data to backend

## Example Flow

1. Customer selects product: Coffee - $2.50
2. Customer chooses KHR currency
3. System shows: ៛10,250 (at rate 4,100)
4. Customer pays via KHQR
5. System stores in database:
   - Amount: $2.50 (USD)
   - Currency: USD
   - Notes: "Customer paid in KHR: ៛10,250 (Exchange rate: 4,100) | MD5: xyz789..."
6. Payment confirmed, adds: "Transaction ID: TXN789 | Bank: ABA Bank | Time: 2025-11-24 10:30:00"

## Reporting

When generating reports:
- All amounts are in USD for easy calculation
- Check notes field to see original payment currency
- Transaction IDs available for bank reconciliation
