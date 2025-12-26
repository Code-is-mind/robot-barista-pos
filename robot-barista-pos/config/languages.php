<?php
// Language translations for kiosk

$translations = [
    'en' => [
        // Header
        'robot_barista' => 'Robot Barista',
        'self_service_kiosk' => 'Self-Service Kiosk',
        'back_to_products' => 'Back to Products',
        
        // Categories
        'all' => 'All',
        
        // Product page
        'your_name_optional' => 'Your Name (Optional)',
        'enter_your_name' => 'Enter your name',
        'size' => 'Size',
        'quantity' => 'Quantity',
        'cups' => 'cups',
        'order_summary' => 'Order Summary',
        'base_price' => 'Base Price',
        'size_modifier' => 'Size Modifier',
        'subtotal' => 'Subtotal',
        'tax' => 'Tax',
        'total' => 'Total',
        'order_now' => 'Order Now',
        
        // Payment modal
        'scan_to_pay' => 'Scan to Pay',
        'amount' => 'Amount',
        'waiting_for_payment' => 'Waiting for payment...',
        'time_remaining' => 'Time remaining',
        'payment_auto_cancel' => 'Payment will auto-cancel after 2 minutes',
        'cancel_payment' => 'Cancel Payment',
        
        // Receipt modal
        'payment_successful' => 'Payment Successful!',
        'need_receipt' => 'Do you need a receipt?',
        'yes_print' => 'Yes, Print',
        'no_thanks' => 'No, Thanks',
        'auto_closing_in' => 'Auto-closing in',
        'seconds' => 'seconds',
        
        // Preparing modal
        'preparing_order' => 'Preparing Your Order',
        'robot_making_drink' => 'Our robot barista is making your drink...',
        'please_wait' => 'Please wait',
        
        // Messages
        'payment_timeout' => 'Payment timeout - Order cancelled',
        'sale_completed' => 'Sale Completed!',
        'no_products_available' => 'No products available',
    ],
    
    'kh' => [
        // Header
        'robot_barista' => 'រ៉ូបូតធ្វើកាហ្វេ',
        'self_service_kiosk' => 'ម៉ាស៊ីនបម្រើសេវាដោយខ្លួនឯង',
        'back_to_products' => 'ត្រឡប់ទៅផលិតផល',
        
        // Categories
        'all' => 'ទាំងអស់',
        
        // Product page
        'your_name_optional' => 'ឈ្មោះរបស់អ្នក (ស្រេចចិត្ត)',
        'enter_your_name' => 'បញ្ចូលឈ្មោះរបស់អ្នក',
        'size' => 'ទំហំ',
        'quantity' => 'បរិមាណ',
        'cups' => 'ពែង',
        'order_summary' => 'សង្ខេបការបញ្ជាទិញ',
        'base_price' => 'តម្លៃមូលដ្ឋាន',
        'size_modifier' => 'តម្លៃទំហំ',
        'subtotal' => 'សរុបរង',
        'tax' => 'ពន្ធ',
        'total' => 'សរុប',
        'order_now' => 'បញ្ជាទិញឥឡូវនេះ',
        
        // Payment modal
        'scan_to_pay' => 'ស្កេនដើម្បីបង់ប្រាក់',
        'amount' => 'ចំនួនទឹកប្រាក់',
        'waiting_for_payment' => 'កំពុងរង់ចាំការបង់ប្រាក់...',
        'time_remaining' => 'ពេលវេលានៅសល់',
        'payment_auto_cancel' => 'ការបង់ប្រាក់នឹងត្រូវបានលុបចោលស្វ័យប្រវត្តិបន្ទាប់ពី 2 នាទី',
        'cancel_payment' => 'បោះបង់ការបង់ប្រាក់',
        
        // Receipt modal
        'payment_successful' => 'ការបង់ប្រាក់ជោគជ័យ!',
        'need_receipt' => 'តើអ្នកត្រូវការបង្កាន់ដៃទេ?',
        'yes_print' => 'បាទ/ចាស ព្រីន',
        'no_thanks' => 'ទេ អរគុណ',
        'auto_closing_in' => 'បិទស្វ័យប្រវត្តិក្នុងរយៈពេល',
        'seconds' => 'វិនាទី',
        
        // Preparing modal
        'preparing_order' => 'កំពុងរៀបចំការបញ្ជាទិញរបស់អ្នក',
        'robot_making_drink' => 'រ៉ូបូតរបស់យើងកំពុងធ្វើភេសជ្ជៈរបស់អ្នក...',
        'please_wait' => 'សូមរង់ចាំ',
        
        // Messages
        'payment_timeout' => 'អស់ពេលបង់ប្រាក់ - ការបញ្ជាទិញត្រូវបានលុបចោល',
        'sale_completed' => 'ការលក់បានបញ្ចប់!',
        'no_products_available' => 'មិនមានផលិតផល',
    ]
];

function getLang() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['kiosk_lang'] ?? 'en';
}

function setLang($lang) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['kiosk_lang'] = in_array($lang, ['en', 'kh']) ? $lang : 'en';
}

function t($key) {
    global $translations;
    $lang = getLang();
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}
