#FROM php:7.4-fpm-alpine as base
FROM php:7.4-fpm as base

RUN apt-get update && apt-get install -y bsdmainutils procps git zip unzip

COPY php-fpm.conf /usr/local/etc/

COPY ./testBase /proj
COPY composer.json /proj/

WORKDIR /proj

RUN chmod +x ./stage.sh && ./stage.sh

#RUN rm -rf /proj

FROM base as testBase

COPY ./testRun /proj/
#
RUN apt-get install -y iproute2

RUN php composer.phar install

FROM testBase as testScripts

RUN vendor/bin/phpunit --testdox tests

# php-fpm sends its logs to
# /usr/local/var/log/error_log 2>&1
#
# php-fpm is configured by
# /usr/local/etc/php-fpm.conf

#RUN ./stage.sh

#COPY testBase /proj
#
#WORKDIR /proj
#
#RUN chmod -R +x stage.sh && ./stage.sh
#
#FROM testBase as testRun
#
#ARG TEST_RUN_ID=nil
#
#COPY testRun /proj
#
#COPY testRun/image_root /
#
#RUN chmod -R +x stage.sh && ./stage.sh
#
#EXPOSE 9000
