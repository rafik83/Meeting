FROM php:7.4-apache-buster

RUN set -eux \
  && mkdir -p /usr/share/man/man1 \
  && apt-get update \
  && apt-get install -y --no-install-recommends \
  ca-certificates \
  curl \
  gettext-base \
  libbz2-dev \
  libfreetype6-dev \
  libfreetype6-dev \
  libicu-dev \
  libjpeg-dev \
  libjpeg62-turbo-dev \
  libmemcached-dev \
  libmcrypt-dev \
  libpng-dev \
  libxml2-dev \
  libxslt-dev \
  libzip-dev \
  unzip \
  msmtp \
  python-minimal \
  vim \
  zip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN set -x \
  && docker-php-ext-configure intl --enable-intl \
  && docker-php-ext-configure soap --enable-soap \
  && docker-php-ext-configure opcache --enable-opcache \
  && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
  && docker-php-ext-install \
  bcmath \
  bz2 \
  calendar \
  exif \
  gd \
  gettext \
  intl \
  mysqli \
  opcache \
  pcntl \
  pdo_mysql \
  shmop \
  soap \
  sockets \
  sysvmsg \
  sysvsem \
  sysvshm \
  xmlrpc \
  xsl \
  zip

RUN pecl install redis lzf timezonedb \
  && yes '' | pecl install \
  && docker-php-ext-enable redis lzf timezonedb \
  && rm -rf /tmp/pear

RUN echo 'memory_limit=2G' > $PHP_INI_DIR/conf.d/memory-limit-php.ini

RUN a2enmod expires headers rewrite status ssl proxy proxy_http deflate

# Debug options, if provided using the DEBUG arg.
ARG XDEBUG
RUN if [ ! -z "$XDEBUG" ]; then \
  pecl install xdebug; \
  docker-php-ext-enable xdebug; \
  echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
  echo "xdebug.profiler_enable_trigger = on" >> /usr/local/etc/php/conf.d/xdebug.ini; \
  echo "xdebug.profiler_output_dir = /var/www/html/var/report" >> /usr/local/etc/php/conf.d/xdebug.ini; \
  echo "xdebug.remote_enable = on" >> /usr/local/etc/php/conf.d/xdebug.ini; \
  echo "xdebug.remote_autostart = off" >> /usr/local/etc/php/conf.d/xdebug.ini; \
  echo "xdebug.remote_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
  fi

# Should we set up the config for development (opcode cache ...)?
ENV XDEBUG ${XDEBUG}
ARG DISABLE_OPCACHE="0"
ENV DISABLE_OPCACHE ${DISABLE_OPCACHE}
ARG VERSION=latest
ARG PHP_VARIANT=apache

# NodeJS and Yarn
RUN curl -sSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -
RUN apt-get install nodejs
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update && apt-get install yarn

# Timezone
RUN echo "Europe/Paris" > /etc/timezone

# Prepare /var/www
RUN mkdir -p /var/www/.composer && \
    chown www-data:www-data /var/www/.composer && \
    touch /var/www/.yarnrc && \
    chown www-data:www-data /var/www/.yarnrc && \
    mkdir -p /var/www/.yarn && \
    chown www-data:www-data /var/www/.yarn

# Working directory
WORKDIR /srv
RUN usermod -u 1000 www-data
RUN chown -R www-data:www-data /srv
RUN chmod -R 777 /srv
