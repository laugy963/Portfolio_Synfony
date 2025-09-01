FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y libpq-dev unzip \
    && docker-php-ext-install pdo pdo_pgsql

RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# DocumentRoot sur public/
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Autoriser .htaccess dans public/
RUN echo '<Directory /var/www/html/public>\n    AllowOverride All\n</Directory>' >> /etc/apache2/apache2.conf
