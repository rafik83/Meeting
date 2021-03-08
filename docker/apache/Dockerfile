FROM gcr.io/synalabs-marketplace/github.com/synalabs/spin:bdfd5d7 AS spinnaker

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
  libmagickwand-dev \
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

RUN pecl install redis apcu lzf timezonedb \
  && yes '' | pecl install imagick \
  && docker-php-ext-enable redis apcu lzf imagick timezonedb \
  && rm -rf /tmp/pear

# Install New Relic but disable it by default
RUN \
  curl -L https://download.newrelic.com/php_agent/archive/9.15.0.293/newrelic-php5-9.15.0.293-linux.tar.gz | tar -C /tmp -zx && \
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

# Application specific code
COPY --chown=www-data:www-data . /var/www/proximum-vimeet.project.local/htdocs/current
COPY --chown=www-data:www-data app/config/parameters.yml.dist /var/www/proximum-vimeet.project.local/htdocs/current/app/config/parameters.yml

# Spinnaker CLI binary + Templates
COPY --from=spinnaker /usr/local/bin/spin /usr/local/bin/spin
COPY helm/spinnaker /var/www/spinnaker

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

# General symlink for the shared folder
WORKDIR /var/www/proximum-vimeet.project.local/htdocs
RUN ln -sf /share/shared

# Working directory
WORKDIR /var/www/proximum-vimeet.project.local/htdocs/current
USER www-data

# Composer Install
RUN php bin/composer.phar install --no-interaction --verbose --prefer-dist --optimize-autoloader --no-progress

# NodeJS Install
RUN make install@prod

# Symfony
ARG APP_ENV
ARG APP_SECRET

# OpenL10N fetch
ARG OPENL10N_UPDATE="false"
ARG OPENL10N_HOSTNAME
ARG OPENL10N_SSL
ARG OPENL10N_USERNAME
ARG OPENL10N_PASSWORD
RUN if [ "$OPENL10N_UPDATE" = "true" ]; then \
      envsubst < docker/apache/etc/symfony/openl10n.yml > .openl10n.yml && \
      php bin/console vimeet:translations:update; \
    fi

ARG VCS_REF
ENV APP_VERSION=${VCS_REF:-unkown}

# Cleanup
RUN rm -f app/config/parameters.yml && \
    rm -f public/*_dev.php && \
    rm -f public/*_test.php && \
    rm -rf public/uploads && \
    rm -rf public/css && \
    rm -rf var/logs

# Symlinks
RUN ln -sfn /share/shared/web/media public/ && \
    ln -sfn /share/shared/web/uploads public/ && \
    ln -sfn /share/shared/web/css public/ && \
    ln -sfn /share/shared/var/logs var/ && \
    ln -sfn /share/shared/var/shared_uploaded_files var/ && \
    ln -sfn /share/shared/var/participants_imports var/ && \
    ln -sfn /share/shared/app/config/parameters.yml app/config/parameters.yml

USER root
