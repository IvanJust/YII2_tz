FROM php:8.1-apache

# Установка необходимых пакетов
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libcurl4-openssl-dev \
    libzip-dev \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libgd-dev \
    imagemagick \
    libicu-dev \
    locales \
    gettext

# Установка расширений PHP
RUN docker-php-ext-install pdo_pgsql mbstring gd curl zip iconv

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Копирование файлов проекта
COPY . /var/www/my-yii2-app

# Настройка рабочей директории
WORKDIR /var/www/my-yii2-app

# Установка зависимостей с помощью Composer
RUN composer install --no-dev

# Установка Nginx
RUN apt-get update && apt-get install -y nginx

# Настройка Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Настройка апача
#  COPY apache.conf /etc/apache2/sites-available/000-default.conf
#  RUN a2ensite 000-default.conf

# Запуск Nginx
CMD ["nginx", "-g", "daemon off;"]