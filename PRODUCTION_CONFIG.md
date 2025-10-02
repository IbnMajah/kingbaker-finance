# Production Server Configuration for Image Uploads

## Problem
Getting `PostTooLargeException` when uploading more than 5 images in production (Ubuntu server with Apache and PHP 8.3).

## Root Cause
The error occurs because the HTTP POST request size exceeds the server's configured limits. This is controlled by multiple PHP and Apache settings.

## Solutions

### 1. PHP Configuration (php.ini)
Add or update these settings in your `php.ini` file:

```ini
; Maximum allowed size for uploaded files
upload_max_filesize = 10M

; Maximum allowed size for POST data
post_max_size = 50M

; Maximum execution time for scripts
max_execution_time = 300

; Maximum input time for scripts
max_input_time = 300

; Maximum memory limit
memory_limit = 256M

; Maximum number of files that can be uploaded via a single request
max_file_uploads = 20
```

### 2. Apache Configuration (.htaccess)
Add these directives to your `.htaccess` file in the public directory:

```apache
# Increase upload limits
php_value upload_max_filesize 10M
php_value post_max_size 50M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 256M
php_value max_file_uploads 20

# Alternative: Use php_admin_value if php_value doesn't work
# php_admin_value upload_max_filesize 10M
# php_admin_value post_max_size 50M
# php_admin_value max_execution_time 300
# php_admin_value max_input_time 300
# php_admin_value memory_limit 256M
# php_admin_value max_file_uploads 20
```

### 3. Apache Virtual Host Configuration
If you have access to the Apache virtual host configuration, add these directives:

```apache
<VirtualHost *:80>
    # ... other configuration ...
    
    # Increase upload limits
    LimitRequestBody 52428800  # 50MB in bytes
    
    # PHP settings
    php_value upload_max_filesize 10M
    php_value post_max_size 50M
    php_value max_execution_time 300
    php_value max_input_time 300
    php_value memory_limit 256M
    php_value max_file_uploads 20
</VirtualHost>
```

### 4. Nginx Configuration (if using Nginx)
If you're using Nginx as a reverse proxy, add these settings:

```nginx
server {
    # ... other configuration ...
    
    client_max_body_size 50M;
    client_body_timeout 300s;
    client_header_timeout 300s;
    
    location ~ \.php$ {
        # ... other configuration ...
        fastcgi_read_timeout 300s;
        fastcgi_send_timeout 300s;
    }
}
```

## Implementation Steps

### Step 1: Update .htaccess
1. Add the PHP configuration directives to your `.htaccess` file
2. Test with a small upload first
3. Gradually increase limits if needed

### Step 2: Update php.ini
1. Locate your `php.ini` file: `php --ini`
2. Edit the file with the settings above
3. Restart Apache: `sudo systemctl restart apache2`

### Step 3: Verify Configuration
1. Create a PHP info file to check settings:
```php
<?php
phpinfo();
?>
```
2. Look for the values you set to ensure they're active

### Step 4: Test Upload
1. Try uploading 6+ images
2. Check server logs if issues persist
3. Monitor memory usage during uploads

## Alternative Solutions

### 1. Chunked Upload
Implement client-side chunked upload for large files:
- Split large files into smaller chunks
- Upload chunks sequentially
- Reassemble on server

### 2. Direct S3 Upload
Use AWS S3 or similar service for direct uploads:
- Generate signed URLs on server
- Upload directly from client to S3
- Bypass server upload limits

### 3. Queue-based Processing
Process uploads asynchronously:
- Accept upload requests immediately
- Queue file processing
- Notify when complete

## Monitoring

### Check Current Settings
```bash
# Check PHP configuration
php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time|memory_limit)"

# Check Apache configuration
apache2ctl -S

# Check server logs
tail -f /var/log/apache2/error.log
```

### Recommended Limits
- `upload_max_filesize`: 10M (per file)
- `post_max_size`: 50M (total request)
- `max_file_uploads`: 20 (number of files)
- `memory_limit`: 256M (script memory)
- `max_execution_time`: 300s (5 minutes)

## Troubleshooting

### Common Issues
1. **Settings not taking effect**: Restart Apache after changes
2. **Still getting errors**: Check if settings are overridden elsewhere
3. **Memory issues**: Increase `memory_limit` further
4. **Timeout issues**: Increase `max_execution_time`

### Debug Commands
```bash
# Check if Apache can read .htaccess
apache2ctl configtest

# Check PHP settings
php -m | grep -i upload

# Check disk space
df -h

# Check memory usage
free -h
```

## Security Considerations

1. **File Type Validation**: Ensure only allowed file types are uploaded
2. **File Size Limits**: Don't set limits too high to prevent abuse
3. **Virus Scanning**: Consider implementing virus scanning for uploads
4. **Rate Limiting**: Implement rate limiting for upload endpoints
5. **Storage Cleanup**: Regularly clean up old/unused files

## Notes

- The `post_max_size` must be larger than `upload_max_filesize`
- If using multiple file uploads, `post_max_size` should accommodate all files
- Consider the server's available memory and disk space
- Test thoroughly in a staging environment first
