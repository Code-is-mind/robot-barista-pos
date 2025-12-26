<?php
/**
 * Payment API Configuration - EXAMPLE FILE
 * 
 * Copy this file to payment.php and update with your credentials
 * 
 * SECURITY: Never commit payment.php to version control
 */

// Bakong API Configuration
// UPDATE THESE VALUES WITH YOUR ACTUAL API CREDENTIALS
define('BAKONG_API_URL', 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5');
define('BAKONG_API_TOKEN', 'your_bakong_api_token_here');

// Merchant Information for KHQR Generation
// UPDATE THESE VALUES WITH YOUR MERCHANT INFORMATION
define('MERCHANT_ACCOUNT_ID', 'your_merchant_account@bank');
define('MERCHANT_NAME', 'Your Business Name');
define('MERCHANT_CITY', 'Your City');
define('MERCHANT_MOBILE', 'your_phone_number');

// API Settings
define('PAYMENT_CHECK_INTERVAL', 1000); // milliseconds (1 second)
define('PAYMENT_CHECK_MAX_ATTEMPTS', 30); // 30 seconds

/**
 * Get Bakong API Configuration
 * @return array
 */
function getBakongConfig() {
    return [
        'api_url' => BAKONG_API_URL,
        'api_token' => BAKONG_API_TOKEN,
        'merchant' => [
            'account_id' => MERCHANT_ACCOUNT_ID,
            'name' => MERCHANT_NAME,
            'city' => MERCHANT_CITY,
            'mobile' => MERCHANT_MOBILE
        ],
        'settings' => [
            'check_interval' => PAYMENT_CHECK_INTERVAL,
            'max_attempts' => PAYMENT_CHECK_MAX_ATTEMPTS
        ]
    ];
}