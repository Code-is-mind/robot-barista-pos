# UI Customization & PIN Protection Guide

## Overview
This guide covers the new UI customization and kiosk PIN protection features added to the Robot Barista POS system.

## Features

### 1. UI Customization
Admins can now customize the customer-facing kiosk interface with their own branding:

#### Customizable Elements:
- **Navbar Color**: Changes the top navigation bar color
- **Background Color**: Changes the main page background color
- **Primary/Accent Color**: Changes buttons, links, and accent elements
- **Business Name**: Appears on kiosk, receipts, and bills

#### How to Customize:
1. Log in to the admin panel
2. Navigate to **Settings**
3. Scroll to **Kiosk UI Customization** section
4. Use color pickers to select your brand colors
5. Update the business name in **Business Information** section
6. Click **Save All Settings**

#### Where Changes Apply:
- **Kiosk Interface**: All colors and business name
- **Receipts**: Business name, address, and phone
- **Bills**: Business name and information
- **PIN Verification Page**: Uses your brand colors

### 2. Kiosk PIN Protection

#### Purpose
Prevents unauthorized access to the kiosk when users navigate back from other pages. This is useful for:
- Preventing customers from accessing the kiosk after staff use
- Securing the kiosk in public areas
- Controlling when the kiosk is available

#### How It Works:
- **Direct Access**: First-time visitors can access the kiosk without a PIN
- **Return Access**: When users navigate back from other pages (like admin panel), they must enter a PIN
- **Session-Based**: Once verified, users can browse the kiosk freely until they leave

#### Setting Up PIN Protection:

1. **Set a PIN**:
   - Go to **Admin Settings**
   - Scroll to **Kiosk PIN Protection** section
   - Enter a 4-digit PIN (numbers only)
   - Click **Set PIN & Enable Protection**

2. **Enable/Disable Protection**:
   - Once a PIN is set, you can toggle protection on/off
   - Click the **Enable/Disable** button in the PIN status section

3. **Reset PIN**:
   - Simply enter a new 4-digit PIN
   - Click **Set PIN & Enable Protection**
   - The old PIN will be replaced

4. **Forgot PIN**:
   - Admin can reset the PIN from the settings page
   - No recovery mechanism needed - just set a new PIN

#### Security Features:
- PIN is stored as a secure hash (not plain text)
- Uses PHP's `password_hash()` with bcrypt
- Cannot be retrieved, only reset
- Session-based verification

## Database Changes

### New Settings Added:
```sql
-- UI Customization
ui_navbar_color      -- Navbar background color (hex)
ui_bg_color          -- Page background color (hex)
ui_primary_color     -- Primary/accent color (hex)

-- PIN Protection
kiosk_pin_hash       -- Hashed PIN (bcrypt)
kiosk_pin_enabled    -- Enable/disable PIN protection (0/1)
```

### Migration:
Run the migration file to add these settings:
```bash
mysql -u root -p robot_barista_pos < database/add_ui_customization.sql
```

## Files Modified/Created

### New Files:
- `database/add_ui_customization.sql` - Database migration
- `public/admin/kiosk-pin.php` - PIN management API
- `public/kiosk/verify-pin.php` - PIN verification page
- `UI_CUSTOMIZATION_GUIDE.md` - This documentation

### Modified Files:
- `public/admin/settings.php` - Added UI customization and PIN controls
- `public/kiosk/index.php` - Added PIN check and UI customization
- `print-receipt.php` - Uses business name from settings

## API Endpoints

### Kiosk PIN API (`public/admin/kiosk-pin.php`)

#### Get PIN Status
```http
GET /public/admin/kiosk-pin.php
Authorization: Admin session required

Response:
{
  "success": true,
  "data": {
    "enabled": true,
    "has_pin": true
  }
}
```

#### Set PIN
```http
POST /public/admin/kiosk-pin.php
Content-Type: application/json
Authorization: Admin session required

Body:
{
  "action": "set",
  "pin": "1234"
}

Response:
{
  "success": true,
  "message": "PIN set successfully"
}
```

#### Verify PIN
```http
POST /public/admin/kiosk-pin.php
Content-Type: application/json
Authorization: None required

Body:
{
  "action": "verify",
  "pin": "1234"
}

Response:
{
  "success": true,
  "message": "PIN verified"
}
```

#### Enable/Disable PIN
```http
POST /public/admin/kiosk-pin.php
Content-Type: application/json
Authorization: Admin session required

Body:
{
  "action": "enable"  // or "disable"
}

Response:
{
  "success": true,
  "message": "PIN protection enabled"
}
```

## Usage Examples

### Example 1: Rebranding the Kiosk
```
1. Change business name to "Coffee Paradise"
2. Set navbar color to #8B4513 (brown)
3. Set primary color to #D2691E (chocolate)
4. Set background to #FFF8DC (cornsilk)
5. Save settings
6. Visit kiosk to see changes
```

### Example 2: Securing the Kiosk
```
1. Set PIN to "1234"
2. PIN protection is automatically enabled
3. Direct kiosk access works normally
4. When returning from admin panel, PIN is required
5. Enter "1234" to access kiosk
```

### Example 3: Temporary Disable PIN
```
1. Go to Settings > Kiosk PIN Protection
2. Click "Disable" button
3. Kiosk is now accessible without PIN
4. Click "Enable" to re-enable protection
```

## Best Practices

### UI Customization:
- Use high contrast colors for readability
- Test colors on actual kiosk display
- Keep business name concise (fits on receipts)
- Update all business information for professional receipts

### PIN Protection:
- Use a memorable but not obvious PIN
- Change PIN regularly for security
- Don't share PIN with customers
- Disable PIN during peak hours if needed
- Keep a backup of the PIN in a secure location

## Troubleshooting

### UI Changes Not Showing:
- Clear browser cache
- Hard refresh (Ctrl+F5)
- Check if settings were saved successfully

### PIN Not Working:
- Ensure PIN protection is enabled
- Check if you're entering exactly 4 digits
- Reset PIN from admin settings if forgotten
- Verify database migration was run

### Receipt Not Showing Business Name:
- Check business name is set in settings
- Verify settings are saved
- Test with a new order

## Security Considerations

1. **PIN Storage**: PINs are hashed using bcrypt, never stored in plain text
2. **Session Management**: PIN verification is session-based
3. **Admin Access**: Only admins can set/reset PINs
4. **No Recovery**: Forgotten PINs must be reset by admin
5. **Direct Access**: First-time visitors don't need PIN (by design)

## Future Enhancements

Potential improvements for future versions:
- Logo upload for kiosk header
- Custom fonts selection
- Multiple color themes/presets
- PIN expiration after X hours
- PIN attempt limiting
- Email notification on PIN changes
- Custom receipt footer text
