# syntax=docker/dockerfile:1
FROM php:8.1-cli-bullseye
RUN apt update && apt install gettext & docker-php-ext-install pdo_mysql
RUN mkdir /app
COPY . /app
RUN envsubst < "/app/conf/config.env.json" > "/app/conf/config.json"
CMD ["php", "/app/init.php"]