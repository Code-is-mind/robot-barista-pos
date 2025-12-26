# 🚀 Quick Update Guide

## Need to Update API Key? Here's How:

### 1️⃣ Update Payment Configuration (All in One Place!)

**File:** `config/payment.php` ⚠️ SECURE LOCATION

**Update these lines:**
```php
// Line 11 - API Token
define('BAKONG_API_TOKEN', 'YOUR_NEW_TOKEN_HERE');

// Lines 14-17 - Merchant Info
define('MERCHANT_ACCOUNT_ID', 'your_account@bank');
define('MERCHANT_NAME', 'Your Business Name');
define('MERCHANT_CITY', 'Your City');
define('MERCHANT_MOBILE', '012345678');
```

**Save and test!**

✅ **All payment settings now in ONE secure file!**

---

### 3️⃣ Update Business Info (Receipt)

**Go to:** Admin Panel → Settings

Update:
- Business Name
- Business Address  
- Business Phone

These appear on printed receipts.

---

## 🔍 Where is Everything?

### Payment System Files:

```
📁 Project Root
├── 📁 config/
│   ├── payment.php               ← 🔑 ALL API SETTINGS HERE! (SECURE)
│   ├── payment.example.php       ← Example template
│   ├── database.php              ← Database config
│   ├── .htaccess                 ← Web access blocked
│   └── .gitignore                ← Ignored by Git
│
├── 📁 payment/
│   └── check_transaction.php     ← Uses config/payment.php
│
├── 📁 public/kiosk/
│   ├── product.php               ← Uses config/payment.php
│   ├── create_order.php          ← Order creation
│   ├── print_receipt.php         ← Receipt printing
│   ├── cancel_order.php          ← Cancel orders
│   └── update_order_status.php   ← Update status
│
└── 📁 public/admin/
    └── settings.php              ← Business settings
```

---

## ⚡ Quick Commands

### Test API Connection:
```bash
curl -X POST https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"md5":"test"}'
```

### Check Token Expiration:
Visit: https://jwt.io
Paste your token to see expiration date.

### Run Database Migrations:
```bash
mysql -u root -p robot_barista_pos < database/add_has_modifiers.sql
mysql -u root -p robot_barista_pos < database/add_printer_settings.sql
```

---

## 🆘 Common Issues

### "Payment not detected"
→ Check API token in `config/payment.php` line 11

### "QR code not showing"
→ Check merchant info in `config/payment.php` lines 14-17

### "Printer not working"
→ Go to Admin → Settings → Test Printer

### "Wrong business name on receipt"
→ Update in Admin → Settings

---

## 📞 Need Help?

1. Check `PAYMENT_API_GUIDE.md` for detailed docs
2. Check browser console (F12) for errors
3. Check database `print_logs` table for print errors
4. Contact NBC Bakong support for API issues

---

**Pro Tip:** Always test in a development environment before updating production!
