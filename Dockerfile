FROM php:8.2-apache

RUN apt-get update && \
docker-php-ext-install mysqli pdo pdo_mysql && \
apt-get install -y default-mysql-client

COPY . /var/www/html/

EXPOSE 80
