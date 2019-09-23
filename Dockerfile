FROM php:7.0

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions \
    && sync
RUN install-php-extensions zip xdebug

WORKDIR /app
ENV COMPOSER_NO_INTERACTION=1
COPY --from=composer:1.9.0 /usr/bin/composer /usr/bin/composer
COPY composer.json .
COPY composer.lock .
RUN composer install --no-scripts
COPY . .
RUN composer install



