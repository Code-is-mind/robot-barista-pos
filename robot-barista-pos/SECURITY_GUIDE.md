# 🔒 Security Guide

## ✅ Security Improvements Made

### 1. **Centralized Configuration**
All sensitive credentials now in ONE secure location:
- **File:** `config/payment.php`
- **Protected by:** `.htaccess` (blocks web access)
- **Ignored by Git:** `.gitignore` prevents accidental commits

### 2. **Removed Test Files**
- ❌ Deleted `payment/index.php` (test file with hardcoded credentials)
- ✅ Only production files remain

### 3. **Example Templates**
- ✅ `config/payment.example.php` - Safe template to share
- ✅ `config/database.example.php` - Safe template to share
- ⚠️ Never commit actual `payment.php` or `database.php`

---

## 🔐 Configuration File Security

### Protected Files:
```
config/
├── payment.php          ← 🔒 SENSITIVE (blocked by .htaccess)
├── database.php         ← 🔒 SENSITIVE (blocked by .htaccess)
├── payment.example.php  ← ✅ Safe to share
├── database.example.php ← ✅ Safe to share
├── .htaccess            ← Blocks web access
└── .gitignore           ← Prevents Git commits
```

### What's Protected:
1. **API Token** - Bakong authentication
2. **Merchant Info** - Your business details
3. **Database Credentials** - MySQL access

---

## 🛡️ Security Checklist

### Initial Setup:
- [ ] Copy `config/payment.example.php` to `config/payment.php`
- [ ] Update credentials in `config/payment.php`
- [ ] Copy `config/database.example.php` to `config/database.php`
- [ ] Update credentials in `config/database.php`
- [ ] Verify `.htaccess` exists in `config/` folder
- [ ] Test that `config/payment.php` is not accessible via browser

### File Permissions:
```bash
# Secure config files (Linux/Mac)
chmod 600 config/payment.php
chmod 600 config/database.php
chmod 644 config/.htaccess

# Secure config directory
chmod 755 config/
```

### Git Security:
```bash
# Verify files are ignored
git status

# Should NOT show:
# - config/payment.php
# - config/database.php

# Should show:
# - config/payment.example.php
# - config/database.example.php
```

---

## 🚨 What NOT to Do

### ❌ DON'T:
1. **Commit credentials to Git**
   ```bash
   # BAD - Never do this!
   git add config/payment.php
   git commit -m "Added payment config"
   ```

2. **Share actual config files**
   - Don't email `payment.php`
   - Don't post in chat/forums
   - Don't include in screenshots

3. **Hardcode credentials in code**
   ```php
   // BAD - Don't do this!
   $token = 'eyJhbGc...';
   ```

4. **Leave test files in production**
   - Remove all test/debug files
   - Remove commented-out credentials

### ✅ DO:
1. **Use example files for sharing**
   ```bash
   # GOOD - Share example files
   git add config/payment.example.php
   ```

2. **Use environment variables (advanced)**
   ```php
   // GOOD - Use environment variables
   define('BAKONG_API_TOKEN', getenv('BAKONG_TOKEN'));
   ```

3. **Rotate credentials regularly**
   - Update API token every 6 months
   - Change database password periodically

4. **Keep backups secure**
   - Encrypt backup files
   - Store in secure location
   - Don't commit to Git

---

## 🔍 Testing Security

### Test 1: Web Access Blocked
Try accessing config files via browser:
```
http://your-domain.com/config/payment.php
```
**Expected:** 403 Forbidden or 404 Not Found

### Test 2: Git Ignore Working
```bash
git status
```
**Expected:** `payment.php` and `database.php` not listed

### Test 3: File Permissions
```bash
ls -la config/
```
**Expected:** 
- `payment.php` → `-rw-------` (600)
- `database.php` → `-rw-------` (600)

---

## 📋 Deployment Checklist

### Before Deploying:

1. **Setup Configuration:**
   ```bash
   cp config/payment.example.php config/payment.php
   cp config/database.example.php config/database.php
   nano config/payment.php  # Update credentials
   nano config/database.php # Update credentials
   ```

2. **Set Permissions:**
   ```bash
   chmod 600 config/payment.php
   chmod 600 config/database.php
   chmod 755 config/
   ```

3. **Verify .htaccess:**
   ```bash
   cat config/.htaccess
   # Should contain: Deny from all
   ```

4. **Test Access:**
   - Try accessing config files via browser
   - Should get 403 Forbidden

5. **Verify Git:**
   ```bash
   git status
   # Should NOT show payment.php or database.php
   ```

---

## 🔄 Updating Credentials

### Safe Update Process:

1. **Backup current config:**
   ```bash
   cp config/payment.php config/payment.php.backup
   ```

2. **Update credentials:**
   ```bash
   nano config/payment.php
   # Update BAKONG_API_TOKEN
   # Update MERCHANT_* values
   ```

3. **Test immediately:**
   - Make test order
   - Verify payment works
   - Check QR code generates

4. **If issues, rollback:**
   ```bash
   cp config/payment.php.backup config/payment.php
   ```

5. **Remove backup:**
   ```bash
   rm config/payment.php.backup
   ```

---

## 🌐 Production Security

### HTTPS Required:
```apache
# Force HTTPS in .htaccess (root)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Database Security:
```php
// Use strong password
$password = "Complex!Pass@2024#Secure";

// Restrict database user permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON robot_barista_pos.* TO 'pos_user'@'localhost';
```

### Server Security:
```bash
# Disable directory listing
Options -Indexes

# Hide PHP version
expose_php = Off

# Limit file upload size
upload_max_filesize = 5M
post_max_size = 5M
```

---

## 🚨 Security Incident Response

### If Credentials Compromised:

1. **Immediate Actions:**
   - [ ] Change API token immediately
   - [ ] Change database password
   - [ ] Review access logs
   - [ ] Check for unauthorized orders

2. **Update Configuration:**
   ```bash
   nano config/payment.php
   # Update BAKONG_API_TOKEN
   ```

3. **Notify Stakeholders:**
   - Contact NBC Bakong support
   - Inform team members
   - Document incident

4. **Prevent Future Issues:**
   - Review security practices
   - Update access controls
   - Implement monitoring

---

## 📊 Security Monitoring

### Regular Checks:

**Daily:**
```sql
-- Check for suspicious orders
SELECT * FROM orders 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
AND (total_amount > 1000 OR payment_status = 'Failed');
```

**Weekly:**
```bash
# Check file permissions
ls -la config/

# Check for unauthorized files
find . -name "*.php" -mtime -7

# Review access logs
tail -100 /var/log/apache2/access.log
```

**Monthly:**
- Review API token expiration
- Check for security updates
- Audit user access
- Review payment logs

---

## 📞 Security Support

### If You Suspect a Security Issue:

1. **Don't panic** - Follow incident response plan
2. **Document everything** - Take screenshots, save logs
3. **Contact support:**
   - NBC Bakong: support@nbc.gov.kh
   - Your hosting provider
   - Your development team

### Resources:
- OWASP Security Guide: https://owasp.org
- PHP Security Best Practices: https://www.php.net/manual/en/security.php
- MySQL Security: https://dev.mysql.com/doc/refman/8.0/en/security.html

---

## ✅ Security Summary

### What We've Secured:
- ✅ API credentials in protected config file
- ✅ Web access blocked via .htaccess
- ✅ Git commits prevented via .gitignore
- ✅ Example templates for safe sharing
- ✅ Removed test files with hardcoded credentials

### Your Responsibilities:
- 🔒 Keep `config/payment.php` secure
- 🔒 Never commit credentials to Git
- 🔒 Use HTTPS in production
- 🔒 Rotate credentials regularly
- 🔒 Monitor for suspicious activity

---

**Remember:** Security is an ongoing process, not a one-time setup!

**Last Updated:** 2024  
**Security Level:** Enhanced ✅
