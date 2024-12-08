FROM composer:2.0 AS composer

ADD composer.* ./
ADD src/ src

RUN composer install --optimize-autoloader --prefer-dist --no-progress --no-interaction --ignore-platform-reqs

FROM php:8.2-cli-alpine AS phing
MAINTAINER Phing <info@phing.info>

RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gettext intl sockets tidy xsl zip

WORKDIR /app

ADD bin/phing* bin/
ADD src/ src
ADD etc/ etc

COPY --from=composer /app/vendor/ ./vendor

ENTRYPOINT ["bin/phing"]
