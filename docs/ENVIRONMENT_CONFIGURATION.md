# Environment Configuration Guide

## Overview

The School ERP System now supports seamless operation in both **local** and **production** environments without code changes. The system automatically detects the environment and applies the appropriate configuration.

## Architecture

### Components

1. **EnvironmentDetector** (`includes/EnvironmentDetector.php`)
   - Automatically detects environment (local vs production)
   - Loads environment-specific configuration
   - Provides unified configuration interface

2. **Environment Configuration Files** (`config/environments/`)
   - `local.php` - Local development configuration
   - `production.php` - Production configuration

3. **Main Configuration** (`config/config.php`)
   - Uses EnvironmentDetector to load environment-specific settings
   - Maintains backward compatibility
   - Applies environment-based security and error reporting

## Environment Detection

The system detects the environment using the following priority:

1. **Explicit Definition**: `APP_ENV` constant or `.env` file
2. **Auto-Detection**: Based on server hostname
   - Local: `localhost`, `127.0.0.1`, `.local` domains
   - Production: `tacliinhub.uukowtech.com`
3. **Default**: Falls back to production for security

## Configuration Methods

### Method 1: Environment Files (Recommended)

Configuration files in `config/environments/` are automatically loaded based on detected environment.

**Local Configuration** (`config/environments/local.php`):
```php
return [
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'APP_URL' => 'http://localhost/bti/',
    'DEBUG_MODE' => true,
    // ... other settings
];
```

**Production Configuration** (`config/environments/production.php`):
```php
return [
    'DB_HOST' => 'localhost',
    'DB_USER' => 'schoolerp_user',
    'DB_PASS' => '', // Set via .env
    'APP_URL' => 'https://tacliinhub.uukowtech.com/',
    'DEBUG_MODE' => false,
    // ... other settings
];
```

### Method 2: .env File (Optional)

Create a `.env` file in the root directory for sensitive values:

```env
APP_ENV=production
DB_HOST=localhost
DB_USER=production_user
DB_PASS=secure_password
DB_NAME=schoolerp_db
SETTINGS_ENCRYPTION_KEY=your_encryption_key_here
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
```

**Important**: 
- Never commit `.env` file to version control
- Use `.env.example` as a template
- `.env` values override environment file values

## Local Development Setup

### Automatic Detection

The system automatically detects localhost environments:
- `http://localhost/bti/`
- `http://127.0.0.1/bti/`
- Any domain ending in `.local`

### Manual Override

To explicitly set local environment, create `.env`:
```env
APP_ENV=local
```

### Local Configuration Features

- **Error Display**: Enabled for debugging
- **Debug Mode**: Active
- **HTTPS**: Not required
- **Cache**: Disabled
- **Logging**: Verbose (DEBUG level)

## Production Deployment

### Automatic Detection

The system detects production when:
- Hostname contains `tacliinhub.uukowtech.com`
- Not localhost/127.0.0.1

### Production Configuration Features

- **Error Display**: Disabled (security)
- **Debug Mode**: Disabled
- **HTTPS**: Enforced
- **Cache**: Enabled
- **Logging**: Errors only
- **Security**: Enhanced (secure cookies, HTTPS)

### Production Setup Steps

1. **Upload Files**: Deploy all files to server

2. **Set Environment** (Optional):
   ```env
   APP_ENV=production
   ```

3. **Configure Database**:
   - Update `config/environments/production.php` with production DB credentials
   - Or use `.env` file:
     ```env
     DB_HOST=localhost
     DB_USER=production_user
     DB_PASS=secure_password
     DB_NAME=production_db
     ```

4. **Set Encryption Key**:
   ```env
   SETTINGS_ENCRYPTION_KEY=generate_strong_random_key
   ```
   Generate key: `php -r "echo bin2hex(random_bytes(32));"`

5. **Configure Email**:
   ```env
   SMTP_HOST=smtp.gmail.com
   SMTP_USERNAME=your_email@gmail.com
   SMTP_PASSWORD=your_app_password
   ```

6. **Verify HTTPS**: Ensure SSL certificate is properly configured

7. **Set Permissions**:
   ```bash
   chmod 755 uploads/
   chmod 755 logs/
   chmod 644 .env
   ```

## URL Auto-Detection

The system automatically detects the base URL:

- **Local**: `http://localhost/bti/`
- **Production**: `https://tacliinhub.uukowtech.com/`

Manual override in `.env`:
```env
APP_URL=https://tacliinhub.uukowtech.com/
```

## Security Features

### Environment-Based Security

**Local Environment**:
- HTTP allowed
- Debug mode enabled
- Verbose error reporting
- Cache disabled

**Production Environment**:
- HTTPS enforced
- Debug mode disabled
- Error reporting suppressed
- Secure cookies enabled
- Cache enabled

### Encryption Keys

- Different keys for local vs production
- Environment-aware key generation
- Supports explicit key via `SETTINGS_ENCRYPTION_KEY`

## Database Configuration

### Local
```php
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=schoolerp_db
```

### Production
```php
DB_HOST=localhost
DB_USER=production_user
DB_PASS=secure_password
DB_NAME=production_db
```

## Email Configuration

### Local
- Uses development email settings
- Can use Gmail with app password

### Production
- Uses production email settings
- Credentials from `.env` file
- Professional from address

## JavaScript Configuration

The system automatically provides `APP_URL` to JavaScript:

```javascript
// Automatically set from PHP
window.APP_URL = 'https://tacliinhub.uukowtech.com/';
const APP_URL = window.APP_URL;
```

JavaScript files auto-detect if `APP_URL` is not set.

## Troubleshooting

### Issue: Wrong Environment Detected

**Solution**: Explicitly set in `.env`:
```env
APP_ENV=local
# or
APP_ENV=production
```

### Issue: URLs Not Working

**Solution**: Check `APP_URL` in environment config or set in `.env`:
```env
APP_URL=https://tacliinhub.uukowtech.com/
```

### Issue: Database Connection Fails

**Solution**: 
1. Verify credentials in `config/environments/{environment}.php`
2. Check `.env` file for overrides
3. Ensure database server is accessible

### Issue: HTTPS Redirect Loop

**Solution**: 
1. Verify SSL certificate is valid
2. Check `FORCE_HTTPS` setting in production config
3. Ensure reverse proxy (if any) forwards HTTPS correctly

### Issue: Encryption Key Errors

**Solution**: 
1. Set explicit key in `.env`:
   ```env
   SETTINGS_ENCRYPTION_KEY=your_key_here
   ```
2. Generate new key: `php -r "echo bin2hex(random_bytes(32));"`

## Best Practices

1. **Never commit sensitive data**: Use `.env` for secrets
2. **Use environment files**: For non-sensitive defaults
3. **Test locally first**: Verify configuration before deployment
4. **Use strong encryption keys**: Generate unique keys per environment
5. **Monitor logs**: Check error logs in production
6. **Backup configuration**: Keep secure backups of production `.env`

## Migration from Old Configuration

If upgrading from the old hardcoded configuration:

1. **No code changes needed**: System is backward compatible
2. **Optional**: Create environment files for better organization
3. **Optional**: Use `.env` for sensitive values
4. **Verify**: Test both local and production environments

## File Structure

```
schoolerp/
├── config/
│   ├── config.php (main config, uses EnvironmentDetector)
│   ├── database.php
│   └── environments/
│       ├── local.php
│       └── production.php
├── includes/
│   └── EnvironmentDetector.php
├── .env (create from .env.example, not in git)
└── .env.example (template)
```

## Support

For issues or questions:
- Check error logs: `logs/error.log`
- Review environment detection: Check `APP_ENV` constant
- Verify configuration: Check environment-specific config files

---

**Version**: 2.0.0  
**Last Updated**: 2025-12-25


