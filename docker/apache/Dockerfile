FROM gcr.io/synalabs-marketplace/github.com/synalabs/spin:bdfd5d7 AS spinnaker

FROM php:7.2-apache-buster

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
  libmagickwand-dev \
  unzip \
  msmtp \
  python-minimal \
  vim \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN set -x \
  && docker-php-ext-configure intl --enable-intl \
  && docker-php-ext-configure soap --enable-soap \
  && docker-php-ext-configure opcache --enable-opcache \
  && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
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
  wddx \
  xmlrpc \
  xsl \
  zip

RUN pecl install redis apcu lzf timezonedb \
  && yes '' | pecl install imagick \
  && docker-php-ext-enable redis apcu lzf imagick timezonedb \
  && rm -rf /tmp/pear

# Install New Relic but disable it by default
RUN \
  curl -L https://download.newrelic.com/php_agent/archive/8.7.0.242/newrelic-php5-8.7.0.242-linux.tar.gz | tar -C /tmp -zx && \
  export NR_INSTALL_USE_CP_NOT_LN=1 && \
  export NR_INSTALL_SILENT=1 && \
  /tmp/newrelic-php5-*/newrelic-install install && \
  rm -rf /tmp/newrelic-php5-* /tmp/nrinstall* && \
  mv $PHP_INI_DIR/conf.d/newrelic.ini $PHP_INI_DIR/conf.d/newrelic.ini.dist

COPY docker/apache/etc/php/*.ini* $PHP_INI_DIR/conf.d/
COPY docker/apache/etc/msmtprc /etc/msmtprc

RUN echo 'sendmail_path = "/usr/sbin/msmtp -t -i"' > $PHP_INI_DIR/conf.d/mail.ini
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

COPY docker/apache/entrypoint /usr/local/bin/docker-php-entrypoint
RUN chmod a+rx /usr/local/bin/docker-php-entrypoint
COPY docker/apache/etc/apache.conf /etc/apache2/apache2.conf
COPY docker/apache/etc/apache/docker-php.conf /etc/apache2/conf-available/docker-php.conf
COPY docker/apache/etc/apache/vimeet.conf /etc/apache2/sites-available/000-default.conf

# Should we set up the config for development (opcode cache ...)?
ENV XDEBUG ${XDEBUG}
ARG DISABLE_OPCACHE="0"
ENV DISABLE_OPCACHE ${DISABLE_OPCACHE}
ARG VERSION=latest
ARG PHP_VARIANT=apache

# Spinnaker CLI binary + Templates
COPY --from=spinnaker /usr/local/bin/spin /usr/local/bin/spin
COPY helm/spinnaker /var/www/spinnaker

# Timezone
RUN echo "Europe/Paris" > /etc/timezone

# Prepare /var/www
RUN mkdir -p /var/www/.composer && \
    chown www-data:www-data /var/www/.composer && \
    touch /var/www/.yarnrc && \
    chown www-data:www-data /var/www/.yarnrc && \
    mkdir -p /var/www/.yarn && \
    chown www-data:www-data /var/www/.yarn

# General symlink for the shared folder
WORKDIR /var/www/proximum-vimeet.project.local/htdocs
RUN ln -sf /share/shared

# Working directory
WORKDIR /var/www/proximum-vimeet.project.local/htdocs/current

USER root
