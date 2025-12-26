# Database Migration Guide

## 1. Adding has_modifiers Column

If you already have an existing database, run this migration to add the `has_modifiers` column:

```bash
# Using MySQL command line
mysql -u your_username -p robot_barista_pos < database/add_has_modifiers.sql

# Or using phpMyAdmin
# 1. Open phpMyAdmin
# 2. Select robot_barista_pos database
# 3. Go to SQL tab
# 4. Copy and paste content from add_has_modifiers.sql
# 5. Click Go
```

### What this does:
- Adds `has_modifiers` column to products table
- Sets default value to 1 (enabled) for all existing products
- Allows you to control which products can have size modifiers

### Usage:
- Products with `has_modifiers = 1`: Show size selection (Small, Medium, Large)
- Products with `has_modifiers = 0`: No size selection, fixed price only

---

## 2. Adding Printer Settings

Run this migration to add printer configuration settings:

```bash
mysql -u your_username -p robot_barista_pos < database/add_printer_settings.sql
```

### What this does:
- Adds printer configuration settings (enabled, type, paper width)
- Sets default values for network printer setup
- Enables auto-print functionality

### Printer Setup:
1. Go to Admin Panel → Settings
2. Configure printer IP address and port
3. Enable/disable auto-print
4. Test connection using "Test Printer" button
5. Receipts will auto-print when customers click "Yes, Print"

### Supported Printers:
- ESC/POS compatible thermal printers (80mm or 58mm)
- Network printers (TCP/IP connection)
- USB printers (requires additional setup)
