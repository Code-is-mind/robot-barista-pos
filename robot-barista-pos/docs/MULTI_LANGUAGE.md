# Multi-Language Support for Kiosk

## Overview
The kiosk now supports both English and Khmer languages with easy switching.

## Features
- **Language Toggle**: Button in header to switch between English (EN) and Khmer (ខ្មែរ)
- **Session Persistence**: Language choice is saved in session
- **Khmer Font**: Battambang font automatically loads for Khmer language
- **Full Translation**: All UI text is translated including:
  - Headers and navigation
  - Product page labels
  - Payment modal
  - Receipt modal
  - Preparing modal
  - Toast messages

## How to Use

### For Users
1. Click the language button in the top-right header
2. Toggle between "EN" (English) and "ខ្មែរ" (Khmer)
3. Language persists across pages during the session

### For Developers

#### Adding New Translations
Edit `config/languages.php`:

```php
$translations = [
    'en' => [
        'new_key' => 'English Text',
    ],
    'kh' => [
        'new_key' => 'ខ្មែរ',
    ]
];
```

#### Using Translations in PHP
```php
<?= t('translation_key') ?>
```

#### Using Translations in JavaScript
Add to the i18n object in product.php:
```javascript
const i18n = {
    newKey: '<?= addslashes(t('new_key')) ?>'
};
```

Then use: `i18n.newKey`

## Files Modified
- `config/languages.php` - Translation system and strings
- `public/kiosk/index.php` - Language switcher and translations
- `public/kiosk/product.php` - Product page translations

## Supported Languages
- English (en)
- Khmer (kh)

## Font
Khmer text uses Google Fonts "Battambang" for proper rendering.
