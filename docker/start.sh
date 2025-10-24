#!/bin/sh
set -e

# SQLite depoları
mkdir -p /var/www/html/storage
chown -R www-data:www-data /var/www/html/storage || true
chmod -R 775 /var/www/html/storage || true

exec apache2-foreground
