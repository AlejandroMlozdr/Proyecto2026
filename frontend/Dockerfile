FROM php:8.2-apache

# Habilitar mod_rewrite y curl para PHP
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Copiar archivos de la app
COPY app/ /var/www/html/

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
