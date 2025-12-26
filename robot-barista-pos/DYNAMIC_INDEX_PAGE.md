# Dynamic Index Page

## ✅ Changes Made

The public landing page (`public/index.html`) has been converted to a dynamic PHP page (`public/index.php`) that pulls data from the database.

## 🎨 Dynamic Features

### Database-Driven Content
1. **Business Name** - Shows your custom business name
2. **Navbar Color** - Uses your custom navbar color
3. **Background Color** - Uses your custom background color
4. **Primary Color** - Uses your custom primary/accent color
5. **Background Image** - Shows your custom background image (if set)
6. **Product Count** - Displays total available products
7. **Category Count** - Displays total active categories

### Visual Enhancements
- ✅ **Branded Colors** - All colors match your settings
- ✅ **Background Image Support** - Full-screen background if set
- ✅ **Gradient Fallback** - Beautiful gradient if no image
- ✅ **Glass Effect** - Semi-transparent cards with blur
- ✅ **Hover Effects** - Smooth animations on cards
- ✅ **Responsive Design** - Works on all screen sizes
- ✅ **System Stats** - Shows product and category counts

## 📋 What Changed

### Before (Static HTML)
```html
<!-- Fixed content -->
<h1>Robot Barista POS</h1>
<style>
  body { background: orange gradient; }
</style>
```

### After (Dynamic PHP)
```php
<!-- Dynamic content from database -->
<h1><?= $businessName ?></h1>
<style>
  body { 
    background-color: <?= $bgColor ?>;
    background-image: url('<?= $bgImage ?>');
  }
</style>
```

## 🎯 Features

### Landing Page Now Shows:
1. **Custom Business Name** in header
2. **Custom Colors** throughout design
3. **Background Image** (if uploaded)
4. **Product Count** badge
5. **Category Count** badge
6. **System Overview** stats section
7. **Branded Footer** with business name

### Two Main Sections:
1. **Customer Kiosk Card**
   - Green-themed (uses primary color)
   - Links to kiosk
   - Lists customer features
   
2. **Admin Panel Card**
   - Orange-themed (uses navbar color)
   - Links to admin login
   - Lists admin features

### System Overview Section:
- Product count
- Category count
- Payment method (KHQR)
- Printer type (80mm)

## 🎨 Design Features

### Color System
```css
:root {
  --navbar-color: /* From database */
  --bg-color: /* From database */
  --primary-color: /* From database */
}
```

### Background Handling
- **With Image**: Full-screen cover with blur overlay
- **Without Image**: Gradient from bg-color to navbar-color

### Card Effects
- Semi-transparent white background
- Backdrop blur for glass effect
- Hover animation (lift up)
- Shadow enhancement on hover

## 📁 File Changes

### Deleted
- `public/index.html` - Static HTML file

### Created
- `public/index.php` - Dynamic PHP file

## 🔧 Technical Details

### Database Query
```php
SELECT setting_key, setting_value FROM settings 
WHERE setting_key IN (
  'business_name',
  'ui_navbar_color',
  'ui_bg_color',
  'ui_primary_color',
  'ui_bg_image'
)
```

### Fallback Handling
If database connection fails, uses default values:
- Business Name: "Robot Barista"
- Navbar Color: #f97316 (orange)
- Background Color: #f97316 (orange)
- Primary Color: #16a34a (green)

### Product/Category Counts
```php
SELECT COUNT(*) FROM products WHERE is_available = 1
SELECT COUNT(*) FROM categories WHERE is_active = 1
```

## 🚀 How It Works

1. **Page Load** → Connects to database
2. **Fetch Settings** → Gets UI customization settings
3. **Fetch Stats** → Gets product and category counts
4. **Render Page** → Applies custom colors and content
5. **Display** → Shows branded landing page

## ✨ Benefits

### For Admins:
- ✅ One place to update branding (admin settings)
- ✅ Changes apply everywhere automatically
- ✅ No code editing needed
- ✅ Preview changes immediately

### For Users:
- ✅ Consistent branding across all pages
- ✅ Professional appearance
- ✅ Clear navigation options
- ✅ System information at a glance

## 🎯 Use Cases

### Example 1: Coffee Shop
```
Business Name: "Sunrise Coffee"
Navbar Color: #8B4513 (brown)
Primary Color: #D2691E (chocolate)
Background: coffee-beans.jpg
Result: Fully branded landing page
```

### Example 2: Tea House
```
Business Name: "Zen Tea Garden"
Navbar Color: #556B2F (olive)
Primary Color: #9ACD32 (yellow-green)
Background: tea-leaves.jpg
Result: Calming, branded experience
```

### Example 3: Juice Bar
```
Business Name: "Fresh Squeeze"
Navbar Color: #FF6347 (tomato)
Primary Color: #FF8C00 (orange)
Background: fruits.jpg
Result: Vibrant, energetic landing
```

## 📊 Page Sections

### 1. Header
- Robot icon
- Business name (dynamic)
- Subtitle
- Product/category badges

### 2. Main Cards
- Customer Kiosk (left)
- Admin Panel (right)
- Feature lists
- Action buttons

### 3. System Overview
- Product count
- Category count
- Payment method
- Printer type

### 4. Footer
- Business name
- Version info
- Technology stack

## 🎨 Styling Features

### Glass Morphism
```css
backdrop-filter: blur(10px);
background: rgba(255, 255, 255, 0.95);
```

### Hover Effects
```css
transform: translateY(-5px);
box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
```

### Responsive Grid
```css
grid md:grid-cols-2 gap-8
```

## 🔄 Update Flow

1. **Admin changes settings** → Database updated
2. **User visits landing page** → PHP fetches new settings
3. **Page renders** → Shows updated branding
4. **No cache issues** → Always fresh from database

## ✅ Validation

- ✅ No PHP syntax errors
- ✅ Database connection handled
- ✅ Fallback values provided
- ✅ HTML structure valid
- ✅ CSS properly scoped
- ✅ Responsive design working

## 🎉 Summary

The landing page is now fully dynamic and branded! It automatically reflects all your UI customization settings from the admin panel, including:
- Business name
- Custom colors
- Background image
- Product/category counts

No more editing HTML files - just update your settings in the admin panel and the landing page updates automatically!
