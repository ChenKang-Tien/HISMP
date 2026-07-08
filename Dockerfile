FROM php:8.4-cli

# 一次性安裝前端環境、Linux 依賴庫，以及 PHP 的 pdo_mysql 與 intl 擴充
RUN apt-get update && apt-get install -y \
    nodejs \
    npm \
    libicu-dev \
    && docker-php-ext-install pdo_mysql intl

WORKDIR /app