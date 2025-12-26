# 🚀 Setup Instructions

## Quick Start Guide

### Step 1: Copy Configuration Files

```bash
# Copy payment configuration
cp config/payment.example.php config/payment.php

# Copy database configuration
cp config/database.example.php config/database.php
```

### Step 2: Update Payment Configuration

Edit `config/payment.php`:

```php
// Line 11 - Your Bakong API Token
define('BAKONG_API_TOKEN', 'YOUR_ACTUAL_TOKEN_HERE');

// Lines 14-17 - Your Merchant Information
define('MERCHANT_ACCOUNT_ID', 'your_account@aclb');
define('MERCHANT_NAME', 'Your Business Name');
define('MERCHANT_CITY', 'Phnom Penh');
define('MERCHANT_MOBILE', '012345678');
```

### Step 3: Update Database Configuration

Edit `config/database.php`:

```php
private $host = "localhost";
private $db_name = "robot_barista_pos";
private $username = "root";
private $password = "your_mysql_password";
```

### Step 4: Create Database

```bash
mysql -u root -p < database/schema.sql
```

### Step 5: Secure Configuration Files

```bash
# Set proper permissions (Linux/Mac)
chmod 600 config/payment.php
chmod 600 config/database.php
chmod 755 config/

# Verify .htaccess exists
ls -la config/.htaccess
```

### Step 6: Test System

1. **Login to Admin:**
   - URL: `http://your-domain.com/public/admin/login.html`
   - Username: `admin`
   - Password: `admin123`
   - ⚠️ Change password immediately!

2. **Configure Settings:**
   - Go to Settings
   - Update business information
   - Configure printer (if available)
   - Set exchange rate and tax

3. **Test Kiosk:**
   - URL: `http://your-domain.com/public/kiosk/`
   - Select a product
   - Make test order
   - Verify payment QR generates

---

## 🔒 Security Setup

### Verify Configuration is Protected:

1. **Test web access (should fail):**
   ```
   http://your-domain.com/config/payment.php
   ```
   Expected: 403 Forbidden

2. **Verify Git ignore:**
   ```bash
   git status
   ```
   Should NOT show `payment.php` or `database.php`

3. **Check file permissions:**
   ```bash
   ls -la config/
   ```
   - `payment.php` should be `-rw-------` (600)
   - `database.php` should be `-rw-------` (600)

---

## 📁 File Structure After Setup

```
robot-barista-pos/
├── config/
│   ├── payment.php              ← ✅ Your actual config (secure)
│   ├── database.php             ← ✅ Your actual config (secure)
│   ├── payment.example.php      ← Template
│   ├── database.example.php     ← Template
│   ├── .htaccess                ← Blocks web access
│   └── .gitignore               ← Prevents Git commits
├── database/
│   └── schema.sql               ← Run this to create database
├── payment/
│   └── check_transaction.php    ← Uses config/payment.php
├── public/
│   ├── kiosk/                   ← Customer interface
│   └── admin/                   ← Admin panel
└── [documentation files]
```

---

## ⚙️ Configuration Reference

### Payment Configuration (`config/payment.php`)

| Setting | Description | Example |
|---------|-------------|---------|
| `BAKONG_API_URL` | Bakong API endpoint | `https://api-bakong.nbc.gov.kh/v1/...` |
| `BAKONG_API_TOKEN` | Your API token | `eyJhbGc...` |
| `MERCHANT_ACCOUNT_ID` | Your Bakong account | `john@aclb` |
| `MERCHANT_NAME` | Your business name | `Coffee Shop` |
| `MERCHANT_CITY` | Your city | `Phnom Penh` |
| `MERCHANT_MOBILE` | Your phone | `012345678` |

### Database Configuration (`config/database.php`)

| Setting | Description | Example |
|---------|-------------|---------|
| `$host` | MySQL host | `localhost` |
| `$db_name` | Database name | `robot_barista_pos` |
| `$username` | MySQL user | `root` |
| `$password` | MySQL password | `your_password` |

---

## 🧪 Testing Checklist

After setup, test these features:

### Admin Panel:
- [ ] Login works
- [ ] Dashboard shows data
- [ ] Can add/edit products
- [ ] Can manage categories
- [ ] Can manage modifiers
- [ ] Settings save correctly
- [ ] Printer test works (if configured)

### Kiosk:
- [ ] Products display correctly
- [ ] Can select product
- [ ] Size selection works (if enabled)
- [ ] Quantity +/- works
- [ ] Price calculates correctly
- [ ] QR code generates
- [ ] Payment detection works
- [ ] Receipt modal appears
- [ ] Order completes successfully

### Payment:
- [ ] QR code shows correct amount
- [ ] QR code shows correct merchant
- [ ] Payment detected within 5 seconds
- [ ] Order status updates to "Paid"
- [ ] Receipt can be printed

---

## 🐛 Troubleshooting

### "Config file not found"
```bash
# Make sure you copied the files
ls -la config/payment.php
ls -la config/database.php

# If missing, copy from examples
cp config/payment.example.php config/payment.php
cp config/database.example.php config/database.php
```

### "Database connection failed"
```bash
# Check MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u root -p

# Verify database exists
mysql -u root -p -e "SHOW DATABASES LIKE 'robot_barista_pos';"
```

### "Payment not detected"
1. Check API token in `config/payment.php`
2. Verify merchant info is correct
3. Check browser console for errors
4. Test API connection:
   ```bash
   curl -X POST https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5 \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"md5":"test"}'
   ```

### "QR code not showing"
1. Check browser console for errors
2. Verify KHQR library loaded
3. Check merchant info in `config/payment.php`
4. Clear browser cache

---

## 📚 Next Steps

After successful setup:

1. **Change Admin Password:**
   - Login to admin panel
   - Go to user settings
   - Change default password

2. **Add Your Products:**
   - Go to Admin → Products
   - Add your menu items
   - Upload product images
   - Set prices

3. **Configure Modifiers:**
   - Go to Admin → Modifiers
   - Add sizes (Small, Medium, Large)
   - Add toppings if needed
   - Set prices

4. **Update Settings:**
   - Go to Admin → Settings
   - Update business information
   - Set exchange rate
   - Configure printer
   - Test printer connection

5. **Test Complete Flow:**
   - Make test order from kiosk
   - Pay with small amount
   - Verify receipt prints
   - Check order in admin

6. **Go Live:**
   - Enable HTTPS
   - Set up backups
   - Monitor system
   - Train staff

---

## 📞 Need Help?

### Documentation:
- `README.md` - Main documentation
- `SECURITY_GUIDE.md` - Security best practices
- `PAYMENT_API_GUIDE.md` - Payment API details
- `QUICK_UPDATE_GUIDE.md` - Quick reference

### Support:
- NBC Bakong: support@nbc.gov.kh
- Phone: +855 23 001 104

---

**Congratulations! Your system is ready to use! 🎉**

**Last Updated:** 2024  
**Setup Time:** ~15 minutes
