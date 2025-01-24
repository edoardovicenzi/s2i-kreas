FROM php:8.2-fpm

RUN apt-get update && apt-get install -y  \
    vim \
    --no-install-recommends

RUN docker-php-ext-install mysqli pdo pdo_mysql
