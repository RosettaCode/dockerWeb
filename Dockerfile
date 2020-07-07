FROM php:7.4-fpm-alpine as base

RUN mkdir -p /proj

COPY composer.json /proj/

FROM base as testBase
# Let's make sure the base image we're operating on has working PHP to our needs.

COPY testBase /proj

WORKDIR /proj

RUN chmod -R +x stage.sh && ./stage.sh

FROM testBase as testRun

ARG TEST_RUN_ID=nil

COPY testRun /proj

COPY testRun/image_root /

RUN chmod -R +x stage.sh && ./stage.sh

EXPOSE 9000