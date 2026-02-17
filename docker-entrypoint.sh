#!/bin/bash
set -e
cd /var/www/html

# Railway injects PORT (e.g. 8080). Apache must listen on it.
if [ -n "$PORT" ]; then
  sed -i "s/^Listen .*/Listen $PORT/" /etc/apache2/ports.conf
  sed -i "s/\*:80/\*:$PORT/g" /etc/apache2/sites-available/*.conf
fi

if [ -n "$DATABASE_URL" ]; then
  php bin/console doctrine:migrations:migrate --no-interaction
fi

exec "$@"
