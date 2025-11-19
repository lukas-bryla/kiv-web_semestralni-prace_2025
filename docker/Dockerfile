# eventhub/docker/Dockerfile
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev zip unzip git nginx \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node.js pro build CKEditoru a Bootstrapu
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www/html

COPY composer.json .
RUN composer install --no-dev --optimize-autoloader

COPY package.json .
RUN npm install && npm run build

COPY . .

RUN chown -R www-data:www-data .

CMD ["php-fpm"]