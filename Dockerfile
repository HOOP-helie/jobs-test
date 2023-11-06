FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install \
    zip \
    && a2enmod rewrite

COPY docker/apache.conf /etc/apache2/sites-enabled/000-default.conf

COPY . /var/www

WORKDIR /var/www

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=2.6.5

RUN composer install --prefer-dist --no-progress --no-interaction

RUN chown -R www-data:www-data /var/www/html

CMD ["apache2-foreground"]