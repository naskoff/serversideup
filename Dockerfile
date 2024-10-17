FROM serversideup/php:8.2-fpm-nginx-alpine AS base

USER root

RUN install-php-extensions intl

FROM base AS dev

ARG UID
ARG GID

USER root

RUN docker-php-serversideup-set-id www-data $UID:$GID  && \
    docker-php-serversideup-set-file-permissions --owner $UID:$GID --service nginx

USER www-data

FROM base AS prod

COPY --chown=www-data:www-data . /var/www/html