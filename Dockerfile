# Use PHP 8.4 to match composer.json "php": ">=8.4"
FROM php:8.4-cli AS php_base

# Install system deps + PHP extensions Symfony/Doctrine need
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libpq-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        intl \
        pdo \
        pdo_pgsql \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ctype, iconv are usually built-in in 8.4; ensure they're enabled
RUN docker-php-ext-enable ctype iconv 2>/dev/null || true

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

WORKDIR /app

# Copy composer files first for better layer cache
COPY composer.json composer.lock ./

# Install PHP deps (no dev in production; use --no-dev when you want prod)
RUN composer install --no-interaction --no-scripts --prefer-dist \
    && composer dump-autoload --optimize --classmap-authoritative

# Copy app (vendor excluded via .dockerignore so we keep composer's vendor)
COPY . .

# Run post-install scripts (cache:clear, assets:install, etc.)
ENV APP_ENV=prod
RUN composer run-script post-install-cmd

# Optional: if you use Node for Tailwind, uncomment and add a node stage
# FROM node:20-alpine AS node_build
# WORKDIR /app
# COPY package.json package-lock.json* ./
# RUN npm ci --omit=dev || npm install --omit=dev
# COPY --from=php_base /app /app
# RUN cp -r node_modules /app/ && cd /app && npm run build  # if you have a build script

# Final image: Apache for serving (symfony/apache-pack)
FROM php:8.4-apache AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        intl \
        pdo \
        pdo_pgsql \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY --from=php_base /app /var/www/html
RUN chown -R www-data:www-data /var/www/html/var

# Document root for Symfony public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80

# Run migrations then Apache (for Railway; DATABASE_URL must be set at runtime)
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
