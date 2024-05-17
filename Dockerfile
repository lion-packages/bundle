FROM php:8.3-apache

ARG DEBIAN_FRONTEND=noninteractive

RUN useradd -m lion && echo 'lion:lion' | chpasswd && usermod -aG sudo lion && usermod -s /bin/bash lion

RUN apt-get update -y \
    && apt-get install -y sudo nano git npm default-mysql-client curl wget unzip cron sendmail libpng-dev libzip-dev \
    && apt-get install -y zlib1g-dev libonig-dev supervisor libevent-dev libssl-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Electron-Vite dependencies
RUN apt-get update -y \
    && apt-get install -y libnss3 mesa-utils libgl1-mesa-glx mesa-utils-extra libx11-xcb1 libxcb-dri3-0 libxtst6 \
    && apt-get install -y libasound2 libgtk-3-0 libcups2 libatk-bridge2.0 libatk1.0 libcanberra-gtk-module \
    && apt-get install -y libcanberra-gtk3-module dbus libdbus-1-3 dbus-user-session \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install ev redis xdebug \
    && docker-php-ext-install mbstring gd pdo_mysql mysqli zip \
    && docker-php-ext-enable gd zip redis xdebug

RUN echo "xdebug.coverage_enable" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN a2enmod rewrite \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . .
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD touch storage/server.log storage/socket.log storage/supervisord.log \
    && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
