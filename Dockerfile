FROM alpine:3.5
MAINTAINER Julien Del-Piccolo <julien@del-piccolo.com>
ARG BRANCH="master"
ARG COMMIT=""
LABEL branch=${BRANCH}
LABEL commit=${COMMIT}

RUN apk update && apk add --no-cache supervisor apache2 php5-apache2 php5-ctype \
 && apk add --virtual=.build-dependencies php5 php5-phar php5-json php5-openssl git \
 && rm -rf /var/www/localhost/htdocs/* \
 && cd /var/www/localhost/htdocs/ \
 && git init && git remote add origin https://github.com/jdel/sspks.git \
 && git fetch origin ${COMMIT} \
 && wget -q -O /usr/local/bin/composer http://getcomposer.org/download/1.3.1/composer.phar \
 && chmod +x /usr/local/bin/composer \
 && cd /var/www/localhost/htdocs \
 && composer install --no-dev \
  ; rm -f /usr/local/bin/composer \
 && apk del .build-dependencies \
 && rm -rf /var/cache/apk/* \
 && mkdir /run/apache2 \
 && ln -sf /dev/stdout /var/log/apache2/access.log \
 && ln -sf /dev/stderr /var/log/apache2/error.log

COPY ./docker/supervisord.conf /usr/local/etc/supervisor/

EXPOSE 80
VOLUME "/var/www/localhost/htdocs/packages"
CMD ["/usr/bin/supervisord", "-c", "/usr/local/etc/supervisor/supervisord.conf"]