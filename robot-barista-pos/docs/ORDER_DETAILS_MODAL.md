# Order Details Modal Feature

## Overview
Added a detailed order view modal in the admin orders page that displays complete order information including payment notes.

## Features

### Order Details Modal Shows:

1. **Order Information**
   - Order Number
   - Date & Time
   - Customer Name
   - Payment Method
   - Payment Status (with color badges)
   - Order Status (with color badges)

2. **Order Items Table**
   - Product Name
   - Size/Modifiers
   - Quantity
   - Unit Price
   - Subtotal per item

3. **Order Totals**
   - Subtotal
   - Tax Amount
   - Total Amount (with currency)

4. **Payment Notes** (Highlighted Section)
   - Original payment currency
   - Amount paid in that currency
   - Exchange rate used
   - MD5 transaction hash
   - Bank transaction ID
   - Bank name
   - Payment timestamp

## How to Use

### For Admin Users:
1. Go to Orders page in admin panel
2. Find the order you want to view
3. Click the eye icon (👁️) in the Actions column
4. Modal opens with complete order details
5. Review all information including payment notes
6. Click X or outside modal to close

### Example Payment Notes Display:

```
Payment Notes:
Customer paid in KHR: ៛200 (Exchange rate: 100) | MD5: abc123...
Transaction ID: TXN789456 | Bank: ABA Bank | Time: 2025-11-24 10:30:00
```

## UI Elements

### Buttons in Actions Column:
- **Eye Icon** (👁️) - View order details (NEW)
- **Print Icon** (🖨️) - Print receipt (existing)

### Modal Features:
- Responsive design
- Scrollable content for long orders
- Color-coded status badges
- Highlighted payment notes section
- Loading spinner while fetching data
- Click outside to close
- Close button (X)

## Files Created/Modified

**Created:**
- `public/admin/get_order_details.php` - API endpoint to fetch order details

**Modified:**
- `public/admin/orders.php` - Added modal and view details button

## Technical Details

### API Endpoint
```
GET /public/admin/get_order_details.php?id={order_id}
```

**Response:**
```json
{
  "success": true,
  "order": {
    "id": 1,
    "order_number": "ORD-20251124-0001",
    "customer_name": "John Doe",
    "total_amount": "2.50",
    "notes": "Customer paid in KHR: ៛200...",
    ...
  },
  "items": [
    {
      "product_name": "Brownie",
      "quantity": 1,
      "unit_price": "2.00",
      "modifiers_json": "{\"size\":\"Small\"}",
      ...
    }
  ]
}
```

### Security
- Session authentication required
- Admin login check
- SQL injection protection (prepared statements)

## Benefits

✅ Quick order review without printing
✅ See complete payment details and notes
✅ View transaction IDs for reconciliation
✅ Check exchange rates used
✅ Verify order items and modifiers
✅ Better customer service (quick lookup)
✅ Audit trail visibility
