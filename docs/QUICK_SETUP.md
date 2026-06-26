# Quick Setup Guide

## Local Development

1. **No configuration needed!** The system auto-detects localhost.

2. **Optional**: Create `.env` for custom settings:
   ```env
   APP_ENV=local
   DB_PASS=your_password
   ```

3. **Start developing**: Access at `http://localhost/bti/`

## Production Deployment

1. **Upload files** to `tacliinhub.uukowtech.com`

2. **Create `.env` file** (copy from `.env.example`):
   ```env
   APP_ENV=production
   DB_HOST=localhost
   DB_USER=production_user
   DB_PASS=secure_password
   DB_NAME=production_db
   SETTINGS_ENCRYPTION_KEY=generate_with_php_config_generate_encryption_key_php
   ```

3. **Generate encryption key**:
   ```bash
   php config/generate-encryption-key.php
   ```

4. **Update production config** (`config/environments/production.php`) if needed

5. **Set permissions**:
   ```bash
   chmod 755 uploads/ logs/
   chmod 644 .env
   ```

6. **Verify**: Access at `https://tacliinhub.uukowtech.com/`

## Environment Detection

The system automatically detects:
- **Local**: `localhost`, `127.0.0.1`, `.local` domains
- **Production**: `tacliinhub.uukowtech.com`

## Manual Override

Set in `.env`:
```env
APP_ENV=local
# or
APP_ENV=production
```

## Troubleshooting

- **Wrong environment?** Set `APP_ENV` in `.env`
- **URLs wrong?** Set `APP_URL` in `.env` or environment config
- **Database error?** Check credentials in environment config or `.env`

For detailed information, see [ENVIRONMENT_CONFIGURATION.md](ENVIRONMENT_CONFIGURATION.md)


