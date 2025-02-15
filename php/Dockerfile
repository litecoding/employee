FROM php:8.3-fpm

WORKDIR /var/www/symfony

RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev libzip4 autoconf pkg-config libxml2-dev \
    && docker-php-ext-configure intl && docker-php-ext-install intl \
    && docker-php-ext-install opcache pdo pdo_mysql xml \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-install zip

RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/40-custom.ini

CMD ["php-fpm"]

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN ln -s $HOME/.symfony*/bin/symfony /usr/local/bin/symfony
