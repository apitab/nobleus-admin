# HWA Admin Panel - Yii2 Project Setup Guide

This is a Yii2 Advanced Project Template application with API and Backend modules that run on separate ports.

## Prerequisites

- PHP >= 7.4.0
- MySQL >= 5.7
- Apache >= 2.4
- Composer
- mod_rewrite enabled in Apache

## Project Structure

```
admin/
├── api/              # API module (REST API)
├── backend/          # Backend admin panel
├── common/           # Shared code between modules
├── console/          # Console commands
├── frontend/         # Frontend module (if needed)
└── vendor/           # Composer dependencies
```

## Installation Steps

### 1. Clone/Download the Project

```bash
cd /var/www/html  # or your preferred web directory
git clone <repository-url> admin
cd admin
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Set Permissions

```bash
# Set proper permissions for runtime and web directories
chmod -R 775 runtime/
chmod -R 775 backend/runtime/
chmod -R 775 api/runtime/
chmod -R 775 backend/web/uploads/
chmod -R 775 backend/web/videos/
chmod -R 775 api/runtime/

# Set ownership (replace www-data with your web server user)
chown -R www-data:www-data runtime/
chown -R www-data:www-data backend/runtime/
chown -R www-data:www-data api/runtime/
chown -R www-data:www-data backend/web/uploads/
chown -R www-data:www-data backend/web/videos/
```

### 4. Database Setup

#### Create MySQL Database

```sql
CREATE DATABASE hwa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hwa_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON hwa.* TO 'hwa_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Configure Database Connection

Edit `common/config/main-local.php`:

```php
<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=hwa',
            'username' => 'hwa_user',
            'password' => 'your_password',
            'charset' => 'utf8mb4',
        ],
    ],
];
```

#### Run Migrations

```bash
# Run migrations to create database tables
php yii migrate
```

### 5. Configure Application

#### Backend Configuration

Edit `backend/config/main-local.php`:

```php
<?php
return [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'your-backend-cookie-validation-key-here',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Your URL rules here
            ],
        ],
    ],
];
```

#### API Configuration

Edit `api/config/main-local.php`:

```php
<?php
return [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'your-api-cookie-validation-key-here',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Your API URL rules here
            ],
        ],
    ],
];
```

## Apache Virtual Host Configuration

### Backend Module (Port 80 or 8080)

Create `/etc/apache2/sites-available/hwa-backend.conf`:

```apache
<VirtualHost *:8080>
    ServerName admin.hwa.local
    ServerAdmin admin@hwa.local
    DocumentRoot /var/www/html/admin/backend/web

    <Directory /var/www/html/admin/backend/web>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Redirect all requests to index.php
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . index.php
    </Directory>

    # PHP Configuration
    php_value upload_max_filesize 100M
    php_value post_max_size 110M
    php_value max_execution_time 300
    php_value max_input_time 300
    php_value memory_limit 256M

    # Error and access logs
    ErrorLog ${APACHE_LOG_DIR}/hwa-backend-error.log
    CustomLog ${APACHE_LOG_DIR}/hwa-backend-access.log combined

    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
</VirtualHost>
```

### API Module (Port 8081)

Create `/etc/apache2/sites-available/hwa-api.conf`:

```apache
<VirtualHost *:8081>
    ServerName api.hwa.local
    ServerAdmin api@hwa.local
    DocumentRoot /var/www/html/admin/api/web

    <Directory /var/www/html/admin/api/web>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Redirect all requests to index.php
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . index.php
    </Directory>

    # PHP Configuration
    php_value upload_max_filesize 100M
    php_value post_max_size 110M
    php_value max_execution_time 300
    php_value max_input_time 300
    php_value memory_limit 256M

    # CORS Headers (if needed for API)
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Authorization, Content-Type"

    # Error and access logs
    ErrorLog ${APACHE_LOG_DIR}/hwa-api-error.log
    CustomLog ${APACHE_LOG_DIR}/hwa-api-access.log combined

    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
</VirtualHost>
```

### Enable Apache Modules

```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl  # If using HTTPS
```

### Configure Apache Ports

Edit `/etc/apache2/ports.conf` to include the ports:

```apache
Listen 80
Listen 8080
Listen 8081

<IfModule ssl_module>
    Listen 443
</IfModule>
```

### Enable Sites and Restart Apache

```bash
# Enable the sites
sudo a2ensite hwa-backend.conf
sudo a2ensite hwa-api.conf

# Test configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

### Alternative: Using Different Ports in httpd.conf (CentOS/RHEL)

If you're using CentOS/RHEL, edit `/etc/httpd/conf/httpd.conf`:

```apache
Listen 80
Listen 8080
Listen 8081
```

Then create configuration files in `/etc/httpd/conf.d/`:

- `hwa-backend.conf` (same content as above)
- `hwa-api.conf` (same content as above)

Restart Apache:

```bash
sudo systemctl restart httpd
```

## Local Development Setup

### Update /etc/hosts

Add these entries to `/etc/hosts`:

```
127.0.0.1   admin.hwa.local
127.0.0.1   api.hwa.local
```

### Access URLs

- **Backend Admin Panel**: http://admin.hwa.local:8080
- **API Endpoint**: http://api.hwa.local:8081

## Environment Configuration

### Development Environment

Set in `backend/web/index.php` and `api/web/index.php`:

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
```

### Production Environment

```php
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');
```

## File Upload Configuration

The project supports file uploads:

- **Backend uploads**: `backend/web/uploads/`
- **Videos**: `backend/web/videos/`

Ensure these directories are writable:

```bash
chmod -R 775 backend/web/uploads/
chmod -R 775 backend/web/videos/
chown -R www-data:www-data backend/web/uploads/
chown -R www-data:www-data backend/web/videos/
```

## Troubleshooting

### Permission Issues

If you encounter permission errors:

```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/html/admin

# Fix permissions
sudo find /var/www/html/admin -type d -exec chmod 755 {} \;
sudo find /var/www/html/admin -type f -exec chmod 644 {} \;
sudo chmod -R 775 /var/www/html/admin/runtime
sudo chmod -R 775 /var/www/html/admin/backend/runtime
sudo chmod -R 775 /var/www/html/admin/api/runtime
```

### Apache Not Starting

Check Apache error logs:

```bash
sudo tail -f /var/log/apache2/error.log
# or for CentOS/RHEL
sudo tail -f /var/log/httpd/error_log
```

### Database Connection Issues

1. Verify MySQL is running: `sudo systemctl status mysql`
2. Check database credentials in `common/config/main-local.php`
3. Test connection: `mysql -u hwa_user -p hwa_admin`

### URL Rewriting Not Working

1. Ensure `mod_rewrite` is enabled: `sudo a2enmod rewrite`
2. Check `.htaccess` files exist in `backend/web/` and `api/web/`
3. Verify `AllowOverride All` is set in Apache configuration

### Port Already in Use

If ports 8080 or 8081 are already in use:

```bash
# Check what's using the port
sudo netstat -tulpn | grep :8080
sudo netstat -tulpn | grep :8081

# Or use lsof
sudo lsof -i :8080
sudo lsof -i :8081
```

Change the ports in:
- Apache virtual host configurations
- `/etc/apache2/ports.conf` or `/etc/httpd/conf/httpd.conf`

## Security Recommendations

1. **Change default passwords** in configuration files
2. **Use HTTPS** in production (configure SSL certificates)
3. **Restrict file permissions** to minimum required
4. **Regular backups** of database and files
5. **Keep dependencies updated**: `composer update`
6. **Set proper file ownership** (www-data or apache user)
7. **Disable directory listing** in Apache config
8. **Use environment variables** for sensitive data in production

## Additional Resources

- [Yii2 Documentation](https://www.yiiframework.com/doc/guide/2.0/en)
- [Apache Virtual Host Documentation](https://httpd.apache.org/docs/2.4/vhosts/)
- [MySQL Documentation](https://dev.mysql.com/doc/)

## Support

For issues or questions, please contact the development team.

