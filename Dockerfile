FROM alpine:3.18
LABEL maintainer="Julien Del-Piccolo <julien@del-piccolo.com>"
LABEL branch=${BRANCH}
LABEL commit=${COMMIT}

USER root

COPY . /var/www/localhost/htdocs/

RUN apk update && apk add --no-cache ca-certificates curl apache2 php81-apache2 php81-phar php81-ctype php81-json \
 && apk add --virtual=.build-dependencies openssl php81 php81-openssl php81-iconv php81-mbstring git \
 && ln -sf /usr/bin/php81 /usr/local/bin/php \
 && rm -f /var/www/localhost/htdocs/index.html \
 && curl -sSL https://getcomposer.org/download/2.5.1/composer.phar -o /usr/local/bin/composer \
 && chmod +x /usr/local/bin/composer \
 && cd /var/www/localhost/htdocs \
 && composer install --no-dev \
 && rm -f /usr/local/bin/composer \
 && apk del .build-dependencies \
 && rm -rf /var/cache/apk/* \
 && mkdir -p /run/apache2 \
 && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/httpd.conf \
 && sed -i 's/^variables_order = "GPCS"/variables_order = "EGPCS"/' /etc/php81/php.ini \
 && ln -sf /dev/stdout /var/log/apache2/access.log \
 && ln -sf /dev/stderr /var/log/apache2/error.log \
 && ln -sf /var/www/localhost/htdocs/packages /packages \
 && ln -sf /var/www/localhost/htdocs/cache /cache

EXPOSE 8080
VOLUME "/packages"
VOLUME "/cache"
CMD ["/usr/sbin/httpd", "-DFOREGROUND"]
