# Background Image Feature

## ✨ New Feature Added

You can now set a custom background image for your kiosk pages!

## 🎨 How It Works

### Admin Settings
1. Go to **Admin Panel → Settings**
2. Scroll to **Kiosk UI Customization** section
3. Find **Background Image (Optional)** section
4. Click **Upload Image** button
5. Select an image file (JPG, PNG, GIF, or WebP)
6. Image will be uploaded and preview shown
7. Background image applies immediately to kiosk

### Features
- ✅ **Upload**: Easy drag-and-drop or click to upload
- ✅ **Preview**: See uploaded image before applying
- ✅ **Remove**: One-click removal with confirmation
- ✅ **Fallback**: Background color shows if no image set
- ✅ **Auto-replace**: New upload replaces old image
- ✅ **File validation**: Only image files accepted
- ✅ **Size limit**: Maximum 5MB per image

## 📋 Technical Details

### Supported Formats
- JPG/JPEG
- PNG
- GIF
- WebP

### Specifications
- **Max file size**: 5MB
- **Recommended resolution**: 1920x1080px or larger
- **Aspect ratio**: Any (will cover screen)
- **Storage**: `public/uploads/` directory

### CSS Implementation
```css
body {
    background-color: var(--bg-color);
    background-image: url('path/to/image.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-repeat: no-repeat;
}
```

## 🗄️ Database

### New Setting
```sql
ui_bg_image VARCHAR -- Filename of background image (empty = no image)
```

### Migration
Run `setup-ui-customization.php` to add the new setting.

## 📁 Files Created/Modified

### New Files
- `public/admin/upload-bg-image.php` - Image upload API

### Modified Files
- `database/add_ui_customization.sql` - Added `ui_bg_image` setting
- `setup-ui-customization.php` - Added `ui_bg_image` to setup
- `public/admin/settings.php` - Added upload UI and JavaScript
- `public/kiosk/index.php` - Added background image support
- `public/kiosk/product.php` - Added background image support

## 🚀 Usage Examples

### Example 1: Coffee Shop Ambiance
```
Upload: coffee-shop-interior.jpg
Result: Warm, inviting background
Effect: Customers feel like they're in a real cafe
```

### Example 2: Brand Pattern
```
Upload: brand-pattern.png
Result: Repeating brand pattern (set to repeat in CSS if needed)
Effect: Strong brand presence
```

### Example 3: Seasonal Theme
```
Upload: christmas-theme.jpg
Result: Holiday-themed background
Effect: Festive atmosphere
```

## 🎯 Best Practices

### Image Selection
- ✅ Use high-quality images
- ✅ Ensure good contrast with text
- ✅ Test on actual kiosk display
- ✅ Consider lighting conditions
- ✅ Avoid busy patterns that distract

### Performance
- ✅ Optimize images before upload
- ✅ Use WebP format for smaller file size
- ✅ Keep under 1MB if possible
- ✅ Test loading speed

### Accessibility
- ✅ Ensure text remains readable
- ✅ Use semi-transparent overlays if needed
- ✅ Test with different screen sizes
- ✅ Provide fallback color

## 🔧 API Endpoints

### Upload Image
```http
POST /public/admin/upload-bg-image.php
Content-Type: multipart/form-data
Authorization: Admin session required

Body:
- bg_image: File

Response:
{
  "success": true,
  "filename": "bg_1234567890_abc123.jpg",
  "url": "../uploads/bg_1234567890_abc123.jpg"
}
```

### Remove Image
```http
DELETE /public/admin/upload-bg-image.php
Authorization: Admin session required

Response:
{
  "success": true,
  "message": "Background image removed"
}
```

## 🎨 How Background Works

### Priority
1. **Background Image** (if uploaded) - Covers entire screen
2. **Background Color** (fallback) - Shows if no image

### Behavior
- Image covers entire viewport
- Fixed attachment (doesn't scroll)
- Centered positioning
- No repeat
- Background color shows while loading

### Combination
You can use both:
- Set a background color that matches your image
- Color shows while image loads
- Color shows if image fails to load

## ✅ Validation

All files validated:
- ✅ No PHP syntax errors
- ✅ No JavaScript errors
- ✅ File upload working
- ✅ Image preview working
- ✅ Remove function working
- ✅ Kiosk display working

## 📊 Feature Summary

### What You Can Do
1. **Upload** background image for kiosk
2. **Preview** image before applying
3. **Remove** image anytime
4. **Replace** image with new one
5. **Fallback** to solid color if no image

### Where It Applies
- ✅ Kiosk main page
- ✅ Product pages
- ✅ All customer-facing pages

### What's Protected
- ✅ File type validation
- ✅ File size limit (5MB)
- ✅ Admin authentication required
- ✅ Old images auto-deleted
- ✅ Secure file handling

## 🎉 Complete Feature Set

Your kiosk now supports:
1. **Navbar Color** - Custom top bar
2. **Background Color** - Solid color fallback
3. **Background Image** - Full-screen image (NEW!)
4. **Primary Color** - Buttons and accents
5. **Business Name** - Branding
6. **Business Info** - Address and phone

## 🔮 Future Enhancements

Potential additions:
- Multiple background images (slideshow)
- Background overlay opacity control
- Image filters (blur, brightness, etc.)
- Video backgrounds
- Animated backgrounds
- Per-page backgrounds

## 📞 Troubleshooting

### Image Not Showing
- Check file was uploaded successfully
- Verify file exists in `public/uploads/`
- Clear browser cache
- Check file permissions

### Upload Fails
- Check file size (max 5MB)
- Verify file type (JPG, PNG, GIF, WebP)
- Check uploads directory exists
- Verify write permissions

### Image Quality Issues
- Use higher resolution image
- Optimize image before upload
- Try different format (WebP recommended)
- Check compression settings

## ✨ Summary

You can now fully customize your kiosk appearance with both colors and background images, creating a unique and branded experience for your customers!
