# Quick Reference Card - UI Customization & PIN Protection

## 🚀 Quick Setup (30 seconds)

1. **Run Setup**: `http://your-domain.com/setup-ui-customization.php`
2. **Login Admin**: Go to Settings
3. **Customize**: Change colors and business name
4. **Save**: Click "Save All Settings"
5. **Done!** Visit kiosk to see changes

## 🎨 UI Customization

### Access
**Admin Panel → Settings → Kiosk UI Customization**

### Options
| Setting | Purpose | Default |
|---------|---------|---------|
| Navbar Color | Top bar color | #16a34a (Green) |
| Background Color | Page background | #f3f4f6 (Gray) |
| Primary Color | Buttons/accents | #16a34a (Green) |
| Business Name | Shown everywhere | Robot Barista |

### Where It Applies
- ✅ Kiosk main page
- ✅ Product pages
- ✅ PIN verification page
- ✅ Receipts (business name)
- ✅ Bills (business name)

## 🔐 PIN Protection

### Access
**Admin Panel → Settings → Kiosk PIN Protection**

### Quick Actions
| Action | Steps |
|--------|-------|
| Set PIN | Enter 4 digits → Click "Set PIN & Enable" |
| Enable | Click "Enable" button |
| Disable | Click "Disable" button |
| Reset | Enter new 4 digits → Click "Set PIN" |

### How It Works
```
Direct Access (first visit) → No PIN needed ✅
Return from other page → PIN required 🔒
Enter correct PIN → Access granted ✅
```

### PIN Entry
- **Format**: 4 digits (0-9)
- **Auto-advance**: Moves to next digit automatically
- **Paste support**: Can paste 4-digit PIN
- **Error feedback**: Shake animation on wrong PIN

## 📋 Common Tasks

### Change Kiosk Colors
```
1. Admin → Settings
2. Scroll to "Kiosk UI Customization"
3. Click color picker
4. Select your color
5. Save All Settings
```

### Set Up PIN Protection
```
1. Admin → Settings
2. Scroll to "Kiosk PIN Protection"
3. Enter 4 digits (e.g., 1234)
4. Click "Set PIN & Enable Protection"
5. Test by navigating to kiosk
```

### Update Business Name
```
1. Admin → Settings
2. Find "Business Information"
3. Update "Business Name"
4. Save All Settings
5. Print test receipt to verify
```

### Disable PIN Temporarily
```
1. Admin → Settings
2. Scroll to "Kiosk PIN Protection"
3. Click "Disable" button
4. PIN protection is now off
```

### Reset Forgotten PIN
```
1. Admin → Settings
2. Scroll to "Kiosk PIN Protection"
3. Enter new 4 digits
4. Click "Set PIN & Enable Protection"
5. Old PIN is replaced
```

## 🎯 Use Case Examples

### Example 1: Rebrand Kiosk
```
Goal: Match company colors
Steps:
  1. Set navbar to #8B4513 (brown)
  2. Set primary to #D2691E (chocolate)
  3. Set background to #FFF8DC (cream)
  4. Update business name
  5. Save
Result: Fully branded kiosk
```

### Example 2: Secure Kiosk
```
Goal: Prevent unauthorized access
Steps:
  1. Set PIN to 1234
  2. Share PIN with staff only
  3. Customers can browse freely
  4. Staff needs PIN to return
Result: Controlled access
```

### Example 3: Professional Receipts
```
Goal: Branded receipts
Steps:
  1. Set business name
  2. Set business address
  3. Set business phone
  4. Save settings
Result: Professional receipts
```

## 🔧 Troubleshooting

### Colors Not Showing
```
Problem: Changed colors but kiosk looks the same
Solution:
  1. Clear browser cache (Ctrl+F5)
  2. Verify settings were saved
  3. Check browser console for errors
```

### PIN Not Working
```
Problem: Can't access kiosk with PIN
Solution:
  1. Verify PIN protection is enabled
  2. Check you're entering exactly 4 digits
  3. Reset PIN from admin settings
```

### Setup Script Fails
```
Problem: Setup script shows error
Solution:
  1. Check database connection
  2. Verify settings table exists
  3. Run main schema.sql first
```

### Receipt Shows Wrong Name
```
Problem: Old business name on receipt
Solution:
  1. Verify business name is saved
  2. Clear any caches
  3. Print new test receipt
```

## 📱 Mobile Testing

### Test Checklist
- [ ] Colors display correctly
- [ ] PIN entry works on mobile keyboard
- [ ] Business name fits on screen
- [ ] Buttons are tappable
- [ ] Receipt prints correctly

## 🔒 Security Best Practices

### PIN Management
- ✅ Use memorable but not obvious PIN
- ✅ Change PIN regularly
- ✅ Don't share with customers
- ✅ Keep backup in secure location
- ✅ Disable during peak hours if needed

### Color Selection
- ✅ Use high contrast colors
- ✅ Test on actual display
- ✅ Consider accessibility
- ✅ Avoid very bright colors
- ✅ Test in different lighting

## 📊 Status Indicators

### PIN Protection Status
| Status | Meaning |
|--------|---------|
| 🟢 PIN protection is ENABLED | Active and working |
| 🟡 PIN is set but DISABLED | PIN exists but not active |
| ⚪ No PIN set | No PIN configured |

### Settings Status
| Indicator | Meaning |
|-----------|---------|
| ✅ Settings saved | Changes applied successfully |
| ❌ Save failed | Error saving settings |
| ⏳ Saving... | Save in progress |

## 🎨 Color Suggestions

### Professional Themes
```
Coffee Shop:
  Navbar: #6F4E37 (Coffee Brown)
  Primary: #8B4513 (Saddle Brown)
  Background: #FFF8DC (Cornsilk)

Modern Cafe:
  Navbar: #2C3E50 (Dark Blue)
  Primary: #3498DB (Bright Blue)
  Background: #ECF0F1 (Light Gray)

Tea House:
  Navbar: #556B2F (Dark Olive)
  Primary: #9ACD32 (Yellow Green)
  Background: #F5F5DC (Beige)

Juice Bar:
  Navbar: #FF6347 (Tomato)
  Primary: #FF8C00 (Dark Orange)
  Background: #FFF5EE (Seashell)
```

## 📞 Quick Help

### Need Help?
1. Check `UI_CUSTOMIZATION_GUIDE.md` for details
2. Review `SETUP_UI_FEATURES.md` for setup
3. See `IMPLEMENTATION_SUMMARY.md` for technical info

### File Locations
```
Setup: setup-ui-customization.php
Admin: public/admin/settings.php
Kiosk: public/kiosk/index.php
PIN: public/kiosk/verify-pin.php
API: public/admin/kiosk-pin.php
```

## ✅ Quick Checklist

### Initial Setup
- [ ] Run setup script
- [ ] Login to admin
- [ ] Set business name
- [ ] Choose colors
- [ ] Save settings
- [ ] Test on kiosk

### PIN Setup (Optional)
- [ ] Decide on 4-digit PIN
- [ ] Enter PIN in settings
- [ ] Click "Set PIN & Enable"
- [ ] Test PIN entry
- [ ] Share PIN with staff
- [ ] Keep backup of PIN

### Testing
- [ ] Visit kiosk - check colors
- [ ] Navigate away and back - test PIN
- [ ] Print receipt - verify business name
- [ ] Test on mobile device
- [ ] Verify all pages match

## 🎉 You're Ready!

Your POS system now has:
- ✅ Custom branding
- ✅ PIN protection
- ✅ Professional receipts
- ✅ Full control

**Enjoy your customized kiosk!** ☕
