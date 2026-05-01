#!/bin/sh

echo "Running migrations..."
php bin/migrate.php

echo "Running seeders..."
php bin/seed.php

echo "Starting php-fpm and nginx..."
php-fpm -D && nginx -g 'daemon off;'
