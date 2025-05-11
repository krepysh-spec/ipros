FROM php:8.3-cli AS base
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    opcache zip intl pcntl pcov gd exif bcmath @composer xdebug