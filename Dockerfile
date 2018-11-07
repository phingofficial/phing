FROM composer:1.7 AS composer

ADD composer.* ./
ADD classes/ classes
RUN mkdir -p test/classes

RUN composer global require hirak/prestissimo --no-plugins --no-scripts
RUN composer install --optimize-autoloader --prefer-dist --no-progress --no-interaction

FROM php:7.2-cli-alpine AS phing
MAINTAINER Phing <info@phing.info>

ADD bin/phing* bin/
ADD classes/ classes
ADD etc/ etc

COPY --from=composer /app/vendor/ ./vendor

ENTRYPOINT ["phing"]
