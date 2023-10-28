FROM php:8.2-apache

RUN apt-get update -y \
    && apt-get install -y nano git npm default-mysql-client curl wget unzip cron sendmail libpng-dev libzip-dev \
    && apt-get install -y zlib1g-dev libonig-dev supervisor libevent-dev libssl-dev \
    && pecl install ev redis \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mbstring gd pdo_mysql zip opcache \
    && docker-php-ext-enable gd zip redis

RUN a2enmod rewrite \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .

RUN useradd -m lion \
    && echo 'lion:lion' | chpasswd \
    && usermod -aG sudo lion \
    && usermod -s /bin/bash lion \
    && chown -R lion:lion /var/www/html

USER lion

RUN composer install --no-interaction --no-scripts --prefer-dist --quiet
