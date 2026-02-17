#!/bin/bash
set -e
cd /var/www/html

# Railway injects PORT (e.g. 8080). Apache must listen on 0.0.0.0:PORT.
export PORT="${PORT:-8080}"
echo "Configuring Apache to listen on 0.0.0.0:$PORT"
sed -i "s/^Listen .*/Listen 0.0.0.0:$PORT/" /etc/apache2/ports.conf
sed -i "s/\*:80/\*:$PORT/g" /etc/apache2/sites-available/*.conf

# Verify Apache config
echo "Verifying Apache configuration..."
apache2ctl configtest || {
  echo "ERROR: Apache configuration test failed!"
  exit 1
}

# Run migrations (non-fatal - app can start even if migrations fail)
if [ -n "$DATABASE_URL" ]; then
  echo "Running database migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction || {
    echo "WARNING: Migrations failed, but continuing startup..."
  }
else
  echo "WARNING: DATABASE_URL not set, skipping migrations"
fi

# Ensure var directories exist and are writable
echo "Ensuring var directories are writable..."
mkdir -p var/cache var/log
chown -R www-data:www-data var || true
chmod -R 775 var || true

# Check required environment variables
if [ -z "$APP_ENV" ]; then
  echo "WARNING: APP_ENV not set, defaulting to prod"
  export APP_ENV=prod
fi

if [ -z "$APP_SECRET" ]; then
  echo "ERROR: APP_SECRET is required but not set!"
  exit 1
fi

# Test PHP can execute
echo "Testing PHP..."
php -v || {
  echo "ERROR: PHP is not working!"
  exit 1
}

echo "Starting Apache on port $PORT..."
exec "$@"
