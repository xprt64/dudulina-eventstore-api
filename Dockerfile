FROM php:7.1-apache

# Install dependencies
RUN apt-get update && apt-get install  -y \
        curl \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libcurl4-openssl-dev \
        pkg-config \
        libssl-dev \
        libssh2-1-dev \
        unixodbc \
        tdsodbc \
        freetds-dev

RUN pecl install mongodb && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/20-mongodb.ini

RUN docker-php-ext-install -j$(nproc) opcache

RUN ["cp", "/etc/apache2/mods-available/rewrite.load", "/etc/apache2/mods-enabled/"]
COPY deploy/php.ini /usr/local/etc/php/conf.d/
COPY deploy/apache-site.conf  /etc/apache2/sites-enabled/000-default.conf


RUN a2enmod ssl

COPY ./ /var/www/

RUN mkdir -p /var/log/app
RUN chmod -R 0777 /var/log/app

EXPOSE 80

### ENVIRONMENT ###
# MONGO_EVENT_STORE_DSN=mongodb://localhost:27017/eventStore
