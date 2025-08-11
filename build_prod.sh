php artisan down &&
echo "Application is now in maintenance mode" &&
git pull origin master &&
echo "Pulled latest changes from master" &&
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev -o &&
echo "Composer install completed" &&
php artisan migrate &&
echo "Migrated successfully!" &&
npm install &&
echo "NPM install completed" &&
sudo chown -R root:staff /var/www/html/finance/ &&
echo "Changed ownership to root:staff" &&
npm run prod && 
echo "Built successfully!" &&
sudo chown -R www-data:www-data /var/www/html/finance &&
echo "Changed ownership to www-data:www-data" &&
php artisan optimize &&
echo "Application optimized" &&
php artisan up &&
echo "Application is now live" &&
sudo service apache2 restart && 
echo "Apache restarted" &&
echo "Deployed successfully!" &&
php artisan optimize &&
echo "Application optimized"

