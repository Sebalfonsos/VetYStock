FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

COPY docker/php/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/vetystock.ini

WORKDIR /var/www/html
COPY . /var/www/html

ENV APP_DOCROOT=/var/www/html/public
