FROM php:7.4-fpm-alpine as base

FROM base as testBase

COPY testBase /testBase

RUN ls -lh /

RUN chmod -R +x /testBase

RUN /testBase/stage.sh
