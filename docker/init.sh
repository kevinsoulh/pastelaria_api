#!/bin/bash

# Script to setup permissions for Laravel application
echo "Configuring permissions..."

# Create necessary directories if they don't exist
mkdir -p /var/www/app/storage/framework/{sessions,views,cache}
mkdir -p /var/www/app/storage/app/{public,laudos}
mkdir -p /var/www/app/bootstrap/cache

# Configure permissions
chmod -R 775 /var/www/app/storage /var/www/app/bootstrap/cache
chown -R www-data:www-data /var/www/app
find /var/www/app/storage -type d -exec chmod 775 {} \;
find /var/www/app/storage -type f -exec chmod 664 {} \;

# Create the symbolic link for storage if it doesn't exist
if [ ! -L /var/www/app/public/storage ]; then
    cd /var/www/app && php artisan storage:link
fi

# Clear cache
cd /var/www/app && php artisan config:clear
cd /var/www/app && php artisan cache:clear

echo "Permissions configured successfully!"
# Execute the original command
exec "$@"
