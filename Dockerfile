FROM composer:1.9 AS composer

ADD composer.* ./
ADD classes/ classes
ADD src/ src

RUN composer global require hirak/prestissimo --no-plugins --no-scripts
RUN composer install --optimize-autoloader --prefer-dist --no-progress --no-interaction

FROM php:7.4-cli-alpine AS phing
MAINTAINER Phing <info@phing.info>

RUN mkdir /app
WORKDIR /app

ADD bin/phing* bin/
ADD classes/ classes
ADD src/ src
ADD etc/ etc

COPY --from=composer /app/vendor/ ./vendor

ENTRYPOINT ["bin/phing"]
