#ARG PHP_VERSION=7.2
FROM php:7.2-fpm

RUN apt-get install make
RUN curl -sSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# PHP Extensions
RUN apt-get update \
  && apt-get install -y libicu-dev \
    libxml2-dev \
    apt-utils \
    libtool \
    libicu-dev \
    openssh-client \
    unzip \
    zlib1g-dev \
    libicu-dev \
    libjpeg-dev \
    libfreetype6-dev \
  && docker-php-ext-install intl soap zip gd pdo_mysql

RUN usermod -u 1000 www-data
RUN chown -R www-data:www-data /srv
RUN chmod -R 777 /srv
