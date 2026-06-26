# Troubleshooting HTTP 500 Error

## Quick Fix Steps

### Step 1: Run Diagnostics
Access the diagnostic script to identify the issue:
```
https://tacliinhub.uukowtech.com/config/diagnose.php
```

This will show you exactly what's wrong.

### Step 2: Common Issues and Fixes

#### Issue 1: Missing EnvironmentDetector.php
**Symptom**: Diagnostic shows "EnvironmentDetector.php missing"

**Fix**: 
- Ensure `includes/EnvironmentDetector.php` is uploaded to the server
- Check file permissions (should be 644)

#### Issue 2: Missing Environment Config Files
**Symptom**: Diagnostic shows "config/environments/production.php missing"

**Fix**:
- Ensure `config/environments/` directory exists
- Upload `config/environments/production.php`
- Upload `config/environments/local.php`

#### Issue 3: PHP Version Too Old
**Symptom**: Diagnostic shows PHP version < 7.0

**Fix**:
- Upgrade PHP to 7.0 or higher
- Contact hosting provider if needed

#### Issue 4: File Permissions
**Symptom**: Diagnostic shows directories not writable

**Fix**:
```bash
chmod 755 logs/
chmod 755 uploads/
chmod 644 config/environments/*.php
chmod 644 includes/EnvironmentDetector.php
```

#### Issue 5: Syntax Error in Config Files
**Symptom**: Diagnostic shows "Fatal error" in config file

**Fix**:
1. Check PHP syntax: `php -l config/environments/production.php`
2. Verify all arrays are properly closed
3. Check for missing semicolons

#### Issue 6: HTTPS Redirect Loop
**Symptom**: Site redirects infinitely

**Fix**:
1. Temporarily disable HTTPS enforcement in `config/environments/production.php`:
   ```php
   'FORCE_HTTPS' => false,
   ```
2. Or ensure SSL certificate is properly configured
3. Check if behind reverse proxy (may need to check `HTTP_X_FORWARDED_PROTO`)

### Step 3: Temporary Fallback

If EnvironmentDetector is causing issues, you can temporarily disable it:

1. Edit `config/config.php`
2. Comment out the EnvironmentDetector section:
   ```php
   // Temporarily disabled
   /*
   if (file_exists(ABSPATH . 'includes/EnvironmentDetector.php')) {
       ...
   }
   */
   ```
3. Add manual configuration:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_pass');
   define('DB_NAME', 'your_db_name');
   define('APP_URL', 'https://tacliinhub.uukowtech.com/');
   ```

### Step 4: Check Error Logs

Check the error log for specific errors:
```bash
tail -f logs/error.log
```

Or via cPanel/FTP, check:
- `logs/error.log`
- `logs/production.log`
- Server error logs (usually in cPanel or hosting control panel)

### Step 5: Verify File Structure

Ensure all files are uploaded:
```
schoolerp/
├── includes/
│   └── EnvironmentDetector.php ✅
├── config/
│   ├── config.php ✅
│   ├── database.php ✅
│   └── environments/
│       ├── local.php ✅
│       └── production.php ✅
└── logs/ (directory exists) ✅
```

### Step 6: Test Environment Detection

Create a simple test file `test-env.php`:
```php
<?php
defined('ABSPATH') or define('ABSPATH', dirname(__FILE__) . '/');
require_once ABSPATH . 'includes/EnvironmentDetector.php';
$env = EnvironmentDetector::getInstance();
echo "Environment: " . $env->getEnvironment();
echo "<br>Base URL: " . $env->getBaseUrl();
?>
```

Access: `https://tacliinhub.uukowtech.com/test-env.php`

### Step 7: Contact Information

If issues persist:
1. Check diagnostic output
2. Review error logs
3. Verify all files are uploaded
4. Check PHP version compatibility

## Prevention

After fixing:
1. Remove `config/diagnose.php` (security)
2. Remove `test-env.php` if created
3. Ensure `.env` file has correct permissions (644)
4. Verify HTTPS is working properly
5. Test all functionality

---

**Last Updated**: December 25, 2025

