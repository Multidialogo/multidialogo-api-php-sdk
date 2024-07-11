FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && \
    apt-get install -y --no-install-recommends zip unzip libicu-dev g++

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y git zlib1g-dev libzip-dev libpng-dev libicu-dev g++

RUN docker-php-ext-install zip
