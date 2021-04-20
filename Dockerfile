FROM composer:2.0 as vendor

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

#
# Application
#
FROM php:8.0-apache

COPY . /var/www/html
COPY --from=vendor /app/vendor/ /var/www/html/vendor/

RUN sed -i -e "s/html/html\/public/g" /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod rewrite
