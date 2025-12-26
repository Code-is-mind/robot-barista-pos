# Printer Setup Guide

## Socket Extension Issue Fixed

The system now works with or without the PHP `sockets` extension enabled.

### Automatic Fallback
- If `sockets` extension is available → Uses it (faster)
- If `sockets` extension is NOT available → Uses `fsockopen()` (fallback)

## Optional: Enable Sockets Extension (Recommended)

If you want better performance, you can enable the sockets extension:

### For XAMPP on Windows:

1. **Open php.ini**
   - Location: `C:\xampp\php\php.ini`
   - Or click "Config" → "PHP (php.ini)" in XAMPP Control Panel

2. **Find this line:**
   ```ini
   ;extension=sockets
   ```

3. **Remove the semicolon:**
   ```ini
   extension=sockets
   ```

4. **Save the file**

5. **Restart Apache** in XAMPP Control Panel

6. **Verify** by visiting: `http://localhost/robot-barista-pos/public/admin/test_printer.php`

### For Linux/Ubuntu:

```bash
sudo apt-get install php-sockets
sudo systemctl restart apache2
```

### For macOS:

```bash
brew install php
# Sockets usually included by default
```

## Testing the Printer

1. Go to Admin Panel → Test Printer
2. Click "Send Test Print"
3. Check your thermal printer for output

## Troubleshooting

### Connection Failed
- ✅ Check printer IP address in Settings
- ✅ Verify printer is powered on
- ✅ Ensure printer is on same network
- ✅ Try pinging printer: `ping 192.168.1.100`
- ✅ Check firewall isn't blocking port 9100

### Print Not Working
- ✅ Verify printer supports ESC/POS commands
- ✅ Check printer port (usually 9100)
- ✅ Test with another ESC/POS tool
- ✅ Check printer paper and status

### Extension Not Loading
- ✅ Make sure you edited the correct php.ini
- ✅ Check for typos (no spaces before "extension")
- ✅ Restart Apache after changes
- ✅ Check phpinfo() to verify

## Files Modified

- `public/admin/test_printer.php` - Added fsockopen fallback
- `public/kiosk/print_receipt.php` - Added fsockopen fallback

## Benefits

✅ Works without sockets extension (using fsockopen)
✅ Better performance with sockets extension
✅ Automatic detection and fallback
✅ No configuration needed
✅ Compatible with all PHP installations
