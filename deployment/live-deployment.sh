# Script to run post deployment in production to configure Statamic caches.

php artisan cache:clear # Clear the Laravel application cache.
php artisan config:cache # Clear and refresh the Laravel config cache.
php artisan route:cache # Clear and refresh the Laravel route cache.
php artisan statamic:stache:warm # Warm the Statamic stache.
php please search:update --all # Update the search index.
php artisan statamic:static:clear # Clear the Statamic static cache (if you use this).
php artisan statamic:peak:warm # Warm the Statamic static cache (if you use this / only available in Peak).
php artisan statamic:assets:generate-presets # Generate all asset presets.
