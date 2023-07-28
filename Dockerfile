# syntax=docker/dockerfile:1
FROM php:8.2-cli-bullseye
RUN apt update && apt install gettext -y && docker-php-ext-install pdo_mysql
RUN mkdir /app
COPY . /app

WORKDIR /app
ENTRYPOINT ["bash", "start.sh"]