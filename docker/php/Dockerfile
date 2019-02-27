FROM php:7.2.10-fpm-alpine

COPY . .
WORKDIR /var/www/html

RUN set -x && \
  apk update && \
  apk add --no-cache libxml2 libxml2-dev curl curl-dev autoconf $PHPIZE_DEPS && \
  docker-php-ext-install opcache mysqli pdo pdo_mysql xml mbstring curl session tokenizer json && \
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer && \
  composer global require hirak/prestissimo && \
  composer install && \
  chmod -R a+w storage/ bootstrap/cache

COPY ./docker/php/config/php.ini /usr/local/etc/php/php.ini
COPY ./docker/php/config/docker-php-ext-opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
