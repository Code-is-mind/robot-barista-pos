# Quick Setup: UI Customization & PIN Protection

## 🚀 Quick Start (3 Steps)

### Step 1: Run Setup Script
Open your browser and navigate to:
```
http://your-domain.com/setup-ui-customization.php
```

This will automatically add all required database settings.

### Step 2: Configure Settings
1. Log in to admin panel
2. Go to **Settings**
3. Customize your UI colors and business name
4. Set up PIN protection (optional)
5. Click **Save All Settings**

### Step 3: Test
1. Visit the kiosk page
2. Verify your brand colors are applied
3. Test PIN protection (if enabled)
4. Print a test receipt to see business name

## ✨ Features Added

### 1. UI Customization
- **Navbar Color**: Customize top bar color
- **Background Color**: Change page background
- **Primary Color**: Buttons and accents
- **Business Name**: Shows everywhere (kiosk, receipts, bills)

### 2. PIN Protection
- **4-Digit PIN**: Secure kiosk access
- **Smart Protection**: Only required when returning from other pages
- **Easy Management**: Enable/disable anytime
- **Secure Storage**: PIN stored as hash

## 📁 Files Added

```
database/
  └── add_ui_customization.sql      # Database migration

public/
  ├── admin/
  │   └── kiosk-pin.php             # PIN management API
  └── kiosk/
      └── verify-pin.php            # PIN verification page

setup-ui-customization.php          # One-click setup script
UI_CUSTOMIZATION_GUIDE.md           # Detailed documentation
SETUP_UI_FEATURES.md                # This file
```

## 📝 Files Modified

```
public/admin/settings.php           # Added UI & PIN controls
public/kiosk/index.php              # Added PIN check & UI customization
print-receipt.php                   # Uses business name from settings
```

## 🎨 Default Colors

The system comes with these default colors:
- **Navbar**: `#16a34a` (Green)
- **Background**: `#f3f4f6` (Light Gray)
- **Primary**: `#16a34a` (Green)

You can change these to match your brand!

## 🔐 PIN Protection Usage

### Setting a PIN:
1. Go to **Admin Settings**
2. Scroll to **Kiosk PIN Protection**
3. Enter 4 digits (e.g., 1234)
4. Click **Set PIN & Enable Protection**

### How It Works:
- ✅ Direct kiosk access = No PIN needed
- 🔒 Returning from other pages = PIN required
- 💡 Perfect for staff/customer separation

### Managing PIN:
- **Enable/Disable**: Toggle button in settings
- **Reset PIN**: Just enter a new 4-digit PIN
- **Forgot PIN**: Admin can reset anytime

## 🎯 Common Use Cases

### Use Case 1: Rebrand Your Kiosk
```
Business: "Coffee Paradise"
Colors: Brown theme (#8B4513)
Result: Fully branded kiosk experience
```

### Use Case 2: Secure Public Kiosk
```
Enable PIN: 1234
Staff knows PIN
Customers can't access after staff use
```

### Use Case 3: Professional Receipts
```
Set business name: "Your Cafe Name"
Set address: "123 Main St, City"
Set phone: "+855 12 345 678"
Result: Professional branded receipts
```

## ⚠️ Important Notes

1. **Run Setup First**: Always run `setup-ui-customization.php` before using features
2. **Clear Cache**: Clear browser cache after changing colors
3. **Test Receipts**: Print test receipt to verify business name
4. **Backup PIN**: Keep PIN in secure location
5. **Database Required**: Ensure main database schema is installed first

## 🔧 Troubleshooting

### Colors Not Showing?
- Clear browser cache (Ctrl+F5)
- Verify settings were saved
- Check browser console for errors

### PIN Not Working?
- Ensure PIN protection is enabled
- Check you're entering exactly 4 digits
- Reset PIN if forgotten

### Setup Script Fails?
- Check database connection
- Verify settings table exists
- Run main schema.sql first

## 📚 Full Documentation

For detailed information, see:
- **UI_CUSTOMIZATION_GUIDE.md** - Complete feature documentation
- **SETUP_INSTRUCTIONS.md** - Main system setup

## 🆘 Need Help?

If you encounter issues:
1. Check the troubleshooting section above
2. Review the full documentation
3. Verify database connection
4. Check PHP error logs

## 🎉 You're All Set!

Your kiosk now has:
- ✅ Custom branding
- ✅ PIN protection
- ✅ Professional receipts
- ✅ Full control over appearance

Enjoy your customized POS system! ☕
