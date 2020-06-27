FROM php:7.4-fpm-alpine as base

FROM base as testBase
# Let's make sure the base image we're operating on has working PHP to our needs.

COPY testBase /testBase

WORKDIR /testBase

RUN chmod -R +x /testBase && /testBase/stage.sh

FROM testBase

COPY testBaseTests /testBase

RUN chmod -R +x /testBase && /testBase/stage.sh
