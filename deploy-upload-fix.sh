#!/bin/bash

# Deploy Upload Fix Script
# This script helps deploy the upload configuration fixes to production

echo "🚀 Deploying Upload Configuration Fixes..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "This script must be run from the Laravel project root directory"
    exit 1
fi

print_status "Found Laravel project directory"

# Backup current .htaccess
if [ -f "public/.htaccess" ]; then
    cp public/.htaccess public/.htaccess.backup.$(date +%Y%m%d_%H%M%S)
    print_status "Backed up current .htaccess file"
fi

# The .htaccess file should already be updated with the new configuration
print_status ".htaccess file updated with upload configuration"

# Create a simple test script for production
cat > public/upload-test.php << 'EOF'
<?php
// Quick upload test - remove after testing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test'])) {
    echo "Upload test: " . (count($_FILES['test']['name']) . " files received");
    echo "<br>Max file size: " . ini_get('upload_max_filesize');
    echo "<br>Post max size: " . ini_get('post_max_size');
    echo "<br>Max file uploads: " . ini_get('max_file_uploads');
} else {
    echo '<form method="post" enctype="multipart/form-data">
        <input type="file" name="test[]" multiple>
        <button type="submit">Test Upload</button>
    </form>';
}
?>
EOF

print_status "Created upload test script at public/upload-test.php"

echo ""
echo "📋 Next Steps for Production Server:"
echo ""

echo "1. Upload the updated files to your production server:"
echo "   - public/.htaccess (with new upload limits)"
echo "   - public/upload-test.php (for testing)"
echo "   - PRODUCTION_CONFIG.md (documentation)"

echo ""
echo "2. Test the configuration:"
echo "   - Visit: https://yourdomain.com/upload-test.php"
echo "   - Try uploading multiple files"
echo "   - Check if the PostTooLargeException is resolved"

echo ""
echo "3. If .htaccess changes don't work, update php.ini:"
echo "   - Find php.ini: php --ini"
echo "   - Update the values as shown in PRODUCTION_CONFIG.md"
echo "   - Restart Apache: sudo systemctl restart apache2"

echo ""
echo "4. Alternative: Update Apache virtual host configuration:"
echo "   - Add LimitRequestBody 52428800 (50MB)"
echo "   - Add PHP settings as shown in PRODUCTION_CONFIG.md"

echo ""
echo "5. Clean up after testing:"
echo "   - Remove public/upload-test.php"
echo "   - Remove public/test-upload-config.php (if uploaded)"

echo ""
print_warning "Important: Test thoroughly in a staging environment first!"
print_warning "Make sure to backup your current configuration before making changes!"

echo ""
echo "🔧 Configuration Summary:"
echo "   - upload_max_filesize: 10M"
echo "   - post_max_size: 50M"
echo "   - max_file_uploads: 20"
echo "   - max_execution_time: 300s"
echo "   - memory_limit: 256M"

echo ""
print_status "Deployment script completed!"
echo "Check PRODUCTION_CONFIG.md for detailed instructions."
