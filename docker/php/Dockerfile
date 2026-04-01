# Basisimage PHP-FPM 8.2
FROM php:8.2-fpm

# Systempakete installieren und mysqli aktivieren
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        unzip \
        git \
        mariadb-client \
    && docker-php-ext-install pdo pdo_mysql mysqli gd

# Optional: Composer installieren
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Arbeitsverzeichnis
WORKDIR /var/www/html

# Alles in den Container kopieren
COPY . /var/www/html

# Rechte setzen
RUN chown -R www-data:www-data /var/www/html