FROM php:7.4-cli

WORKDIR /app

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip unzip libicu-dev g++

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y git zlib1g-dev libzip-dev libpng-dev libicu-dev g++

RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
