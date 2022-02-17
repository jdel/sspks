FROM alpine:edge
LABEL maintainer="Julien Del-Piccolo <julien@del-piccolo.com>"
LABEL branch=${BRANCH}
LABEL commit=${COMMIT}

USER root

COPY . /var/www/localhost/htdocs/

RUN apk update && apk add --no-cache ca-certificates curl apache2 php8-apache2 php8-phar php8-ctype php8-json \
 && apk add --virtual=.build-dependencies openssl php8 php8-openssl php8-iconv php8-mbstring git \
 && rm -f /var/www/localhost/htdocs/index.html \
 && curl -sSL https://getcomposer.org/download/2.2.6/composer.phar -o /usr/local/bin/composer \
 && chmod +x /usr/local/bin/composer \
 && cd /var/www/localhost/htdocs \
 && composer install --no-dev \
  ; rm -f /usr/local/bin/composer \
 && apk del .build-dependencies \
 && rm -rf /var/cache/apk/* \
 && mkdir -p /run/apache2 \
 && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/httpd.conf \
 && sed -i 's/^variables_order = "GPCS"/variables_order = "EGPCS"/' /etc/php8/php.ini \
 && ln -sf /dev/stdout /var/log/apache2/access.log \
 && ln -sf /dev/stderr /var/log/apache2/error.log \
 && ln -sf /var/www/localhost/htdocs/packages /packages \
 && ln -sf /var/www/localhost/htdocs/cache /cache

EXPOSE 8080
VOLUME "/packages"
VOLUME "/cache"
CMD ["/usr/sbin/httpd", "-DFOREGROUND"]
