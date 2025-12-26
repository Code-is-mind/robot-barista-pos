# Configuration Setup

This directory contains configuration files for the Robot Barista POS system.

## Setup Instructions

### 1. Database Configuration

1. Copy `database.example.php` to `database.php`
2. Update the database credentials in `database.php`:
   ```php
   private $host = "localhost";                    // Your database host
   private $db_name = "robot_barista_pos";        // Your database name  
   private $username = "your_db_username";        // Your database username
   private $password = "your_db_password";        // Your database password
   ```

### 2. Payment Configuration

1. Copy `payment.example.php` to `payment.php`
2. Update the payment credentials in `payment.php`:
   ```php
   define('BAKONG_API_TOKEN', 'your_bakong_api_token_here');
   define('MERCHANT_ACCOUNT_ID', 'your_merchant_account@bank');
   define('MERCHANT_NAME', 'Your Business Name');
   define('MERCHANT_CITY', 'Your City');
   define('MERCHANT_MOBILE', 'your_phone_number');
   ```

## Security Notes

- **Never commit `database.php` or `payment.php` to version control**
- These files contain sensitive credentials and are ignored by `.gitignore`
- Only commit the `.example.php` template files
- Keep your actual configuration files local only

## File Structure

```
config/
├── .gitignore              # Ignores sensitive config files
├── README.md              # This file
├── database.example.php   # Database config template
├── payment.example.php    # Payment config template
├── database.php          # Your actual database config (ignored)
├── payment.php           # Your actual payment config (ignored)
└── languages.php         # Language configuration (safe to commit)
```