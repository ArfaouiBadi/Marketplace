#!/bin/bash
set -e
cd /var/www/html
if [ -n "$DATABASE_URL" ]; then
  php bin/console doctrine:migrations:migrate --no-interaction
fi
exec "$@"
