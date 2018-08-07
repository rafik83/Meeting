FROM debian:jessie

RUN apt-get update && apt-get install -y \
    nginx \
    vim

ADD app.conf /etc/nginx/conf.d/default.conf
ADD nginx.conf /etc/nginx/nginx.conf
ADD app_gzip /etc/nginx/conf.d/app_gzip
ADD app_php_fpm /etc/nginx/conf.d/app_php_fpm

RUN echo "upstream php-upstream { server php:9000; }" > /etc/nginx/conf.d/upstream.conf

RUN usermod -u 1000 www-data
RUN chown -R www-data:www-data /srv
RUN chmod -R 777 /srv

CMD ["nginx"]

EXPOSE 80
EXPOSE 443
