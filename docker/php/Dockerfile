#ARG PHP_VERSION=7.2
FROM php:7.2-fpm

RUN apt-get update
RUN apt-get install -y make wget gnupg
RUN curl -sSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -sL https://deb.nodesource.com/setup_8.x | bash -
RUN apt-get install -y nodejs
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update && apt-get install yarn
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory.ini

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
