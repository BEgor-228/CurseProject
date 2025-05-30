FROM php:8.2-apache

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libjpeg-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql zip gd
RUN echo "opcache.enable=1\n\
    opcache.enable_cli=1\n\
    opcache.memory_consumption=128\n\
    opcache.interned_strings_buffer=8\n\
    opcache.max_accelerated_files=10000\n\
    opcache.revalidate_freq=60\n\
    opcache.validate_timestamps=0" > /usr/local/etc/php/conf.d/opcache.ini

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Настройка Apache
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Копирование кода
COPY . /var/www/html

# Права доступа
RUN chown -R www-data:www-data /var/www/html
WORKDIR /var/www/html
