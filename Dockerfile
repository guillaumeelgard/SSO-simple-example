FROM php:8.0-apache

USER root

ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80

COPY --from=composer:2.1.9 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update -qq && \
    apt-get install -qy \
    gnupg \
    zip \
    unzip \
    libzip-dev

RUN useradd -ms /bin/bash simple_user

USER simple_user

CMD bash -c "composer install -d ../app && apache2-foreground"
