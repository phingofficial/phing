FROM php:7.1-cli
MAINTAINER Phing <info@phing.info>

RUN apt-get update -qq -y && \
    apt-get install -y --no-install-recommends git unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN curl -o composer.phar https://getcomposer.org/composer.phar

ADD bin/phing* bin/
ADD classes/ classes
ADD composer.* ./

RUN mkdir -p test/classes

RUN php composer.phar install -o && rm -rf ~/.composer

ENTRYPOINT ["phing"]
