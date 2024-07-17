FROM php:8.2-apache

# Установка PHP-расширений
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libmcrypt-dev \
    libgd-dev \
    libicu-dev \
    libcurl4-openssl-dev \
    libzip-dev \
    && docker-php-ext-install pdo_pgsql mbstring gd curl zip iconv
# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Копирование файлов проекта
COPY . /app

# Переход в рабочую директорию
WORKDIR /app

# Установка зависимостей
RUN composer install

# Копирование конфигурационного файла Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Установка Nginx
RUN apt-get update && apt-get install -y nginx

# Запуск Nginx
CMD ["nginx", "-g", "daemon off;"]