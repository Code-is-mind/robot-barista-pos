# 🤖 Robot Barista POS System

A complete Point of Sale system for self-service kiosks with KHQR payment integration and thermal receipt printing.

---

## ✨ Features

### Customer Kiosk
- 🛍️ Browse products by category
- 📏 Select size (Small/Medium/Large) - configurable per product
- 🔢 Choose quantity with +/- buttons
- 💰 Real-time price calculation with tax
- 💳 KHQR payment (Bakong QR)
- 🧾 Auto-print receipts to thermal printer
- 🌐 Multi-currency (USD/KHR) with auto-conversion

### Admin Panel
- 📊 Dashboard with sales analytics
- 🛒 Product management (add/edit/delete)
- 📂 Category management
- 🎛️ Modifier management (sizes, toppings)
- 📋 Order history and tracking
- 📈 Sales reports (daily/weekly/monthly)
- ⚙️ System settings
- 🖨️ Printer configuration and testing

---

## 🚀 Quick Start

### 1. Database Setup
```bash
# Create database
mysql -u root -p < database/schema.sql

# If updating existing database
mysql -u root -p robot_barista_pos < database/add_has_modifiers.sql
mysql -u root -p robot_barista_pos < database/add_printer_settings.sql
```

### 2. Configure Database Connection
Edit `config/database.php`:
```php
private $host = "localhost";
private $db_name = "robot_barista_pos";
private $username = "root";
private $password = "your_password";
```

### 3. Update Payment API
Edit `payment/check_transaction.php` (Line 21):
```php
$token = 'YOUR_BAKONG_API_TOKEN';
```

Edit `public/kiosk/product.php` (Line 355):
```javascript
const individualInfo = new Info(
    "your_account@bank",
    "Your Business Name",
    "Your City",
    optionalData
);
```

### 4. Configure Settings
1. Login to admin panel: `/public/admin/login.html`
2. Default credentials: `admin` / `admin123`
3. Go to Settings and update:
   - Business information
   - Exchange rate
   - Tax percentage
   - Printer settings

### 5. Test System
1. Test printer: Admin → Test Printer
2. Add products: Admin → Products
3. Test order: Open kiosk at `/public/kiosk/`
4. Make test payment with small amount

---

## 📁 Project Structure

```
robot-barista-pos/
├── config/
│   └── database.php              # Database connection
├── database/
│   ├── schema.sql                # Full database schema
│   ├── add_has_modifiers.sql    # Migration
│   └── add_printer_settings.sql # Migration
├── payment/
│   ├── check_transaction.php    # 🔑 API Token Here
│   └── index.php
├── public/
│   ├── kiosk/                    # Customer interface
│   │   ├── index.php
│   │   ├── product.php           # 🏪 Merchant Info Here
│   │   ├── create_order.php
│   │   ├── print_receipt.php
│   │   └── ...
│   └── admin/                    # Admin panel
│       ├── dashboard.php
│       ├── products.php
│       ├── settings.php
│       └── ...
├── PAYMENT_API_GUIDE.md          # 📖 Detailed docs
├── QUICK_UPDATE_GUIDE.md         # ⚡ Quick reference
├── SYSTEM_ARCHITECTURE.md        # 🏗️ Architecture
└── README.md                     # This file
```

---

## 🔑 Important Configuration Files

| What to Update | File | Line |
|----------------|------|------|
| **API Token** | `payment/check_transaction.php` | 21 |
| **Merchant Info** | `public/kiosk/product.php` | 355 |
| **Database** | `config/database.php` | 10-13 |
| **Business Info** | Admin → Settings | - |
| **Printer** | Admin → Settings | - |

---

## 💳 Payment System

### Bakong KHQR Integration
- **Provider:** National Bank of Cambodia
- **API:** https://api-bakong.nbc.gov.kh
- **Method:** QR Code scanning
- **Currencies:** USD, KHR
- **Real-time:** Payment verification every 1 second

### Payment Flow:
1. Customer orders → QR code generated
2. Customer scans with banking app
3. System checks payment status
4. Order confirmed → Receipt printed
5. Robot prepares drink

---

## 🖨️ Printing System

### Supported Printers:
- ESC/POS thermal printers
- 80mm or 58mm paper width
- Network printers (TCP/IP)
- USB printers (with additional setup)

### Features:
- Silent auto-print (no popup)
- Fallback to browser print
- Print logging and monitoring
- Test connection tool

### Setup:
1. Connect printer to network
2. Find printer IP address
3. Configure in Admin → Settings
4. Test connection
5. Enable auto-print

---

## 📊 Database Schema

### Main Tables:
- `settings` - System configuration
- `categories` - Product categories
- `products` - Product catalog
- `modifiers` - Sizes, toppings, etc.
- `orders` - Order headers
- `order_items` - Order details
- `print_logs` - Print tracking
- `users` - Admin users

---

## 🔒 Security

### Default Admin Credentials:
- **Username:** `admin`
- **Password:** `admin123`
- ⚠️ **Change immediately after installation!**

### Security Checklist:
- [ ] Change admin password
- [ ] Secure API token
- [ ] Use HTTPS in production
- [ ] Restrict file permissions
- [ ] Enable database backups
- [ ] Monitor access logs

---

## 🛠️ Maintenance

### Daily:
- Check order status
- Verify printer working
- Monitor payment success rate

### Weekly:
- Review sales reports
- Check printer paper
- Backup database

### Monthly:
- Update exchange rate
- Review product prices
- Check API token expiration
- Update system if needed

---

## 📖 Documentation

### For Developers:
- `SYSTEM_ARCHITECTURE.md` - System design and flow
- `PAYMENT_API_GUIDE.md` - Payment API details
- `database/README.md` - Database migrations

### For Operators:
- `QUICK_UPDATE_GUIDE.md` - Quick reference
- Admin panel has built-in help

---

## 🐛 Troubleshooting

### Payment Issues:
```bash
# Test API connection
curl -X POST https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"md5":"test"}'
```

### Printer Issues:
1. Go to Admin → Test Printer
2. Check IP address and port
3. Verify network connection
4. Check printer power and paper

### Database Issues:
```bash
# Check connection
mysql -u root -p robot_barista_pos -e "SELECT COUNT(*) FROM products;"

# View recent orders
mysql -u root -p robot_barista_pos -e "SELECT * FROM orders ORDER BY created_at DESC LIMIT 10;"
```

---

## 🆘 Support

### Documentation:
- Check `PAYMENT_API_GUIDE.md` for API issues
- Check `QUICK_UPDATE_GUIDE.md` for quick fixes
- Check browser console (F12) for errors

### External Support:
- **NBC Bakong:** support@nbc.gov.kh
- **Phone:** +855 23 001 104
- **Website:** https://bakong.nbc.gov.kh

---

## 📝 License

Proprietary - All rights reserved

---

## 🎯 System Requirements

### Server:
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Network connectivity for payment API

### Client (Kiosk):
- Modern web browser (Chrome, Firefox, Edge)
- Touch screen recommended
- Network printer (optional)
- Internet connection

### Recommended:
- PHP 8.0+
- MySQL 8.0+
- SSL certificate (HTTPS)
- Dedicated kiosk hardware

---

## 🚀 Deployment

### Production Checklist:
1. ✅ Update all API credentials
2. ✅ Change admin password
3. ✅ Configure business settings
4. ✅ Set up printer
5. ✅ Enable HTTPS
6. ✅ Test full payment flow
7. ✅ Set up database backups
8. ✅ Configure firewall
9. ✅ Monitor system logs
10. ✅ Train staff

---

## 📞 Quick Contact

**Need to update API key?**  
→ See `QUICK_UPDATE_GUIDE.md`

**Payment not working?**  
→ Check `payment/check_transaction.php` line 21

**Printer not working?**  
→ Admin → Settings → Test Printer

**Need help?**  
→ Check `PAYMENT_API_GUIDE.md`

---

**Version:** 1.0  
**Last Updated:** 2024  
**Built with:** PHP, MySQL, JavaScript, Bakong KHQR  
**Made for:** Self-service kiosks in Cambodia 🇰🇭
