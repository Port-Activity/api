FROM php:7.3-fpm-alpine

RUN apk add --no-cache php7-pgsql php7-pdo_pgsql php7-redis php7-pecl-xdebug make
RUN cp /etc/php7/conf.d/* /usr/local/etc/php/conf.d/
RUN cp /usr/local/lib/php/extensions/no-debug-non-zts-20180731/* /usr/lib/php7/modules/
RUN echo "ONLY_FOR_DEV" && rm /usr/local/etc/php/conf.d/xdebug.ini
COPY php/php.ini /usr/local/etc/php/php.ini
COPY php/ext-xdebug.ini /usr/local/etc/php/conf.d/ext-xdebug.ini
#__REMOVE_ON_DEPLOY__RUN rm /usr/local/etc/php/conf.d/ext-xdebug.ini

# Use bind mount instead
#__REMOVE_ON_DEPLOY__COPY src /var/www/src
#__REMOVE_ON_DEPLOY__COPY database /var/www/database
#__REMOVE_ON_DEPLOY__COPY vendor /var/www/vendor

# This checks migration first time anything requested from container
RUN echo 1 > /tmp/first-run
RUN echo 1 > /tmp/first-run.lock
RUN chown www-data:www-data /tmp/first-run
RUN chown www-data:www-data /tmp/first-run.lock

WORKDIR /code
