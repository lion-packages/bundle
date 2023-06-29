FROM php:8.2-apache
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y curl \
    && apt-get install -y zsh \
    && apt-get install -y wget \
    && apt-get install -y git \
    && apt-get install -y unzip \
    && apt-get install -y sudo \
    && apt-get install -y nano \
    && apt-get install -y cron \
    && apt-get install -y sendmail \
    && apt-get install -y libpng-dev \
    && apt-get install -y libzip-dev \
    && apt-get install -y zlib1g-dev \
    && apt-get install -y libonig-dev \
    && apt-get install -y supervisor \
    && apt-get install -y libevent-dev \
    && apt-get install -y libssl-dev \
    && pecl install ev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mbstring \
    && docker-php-ext-install gd \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install zip \
    && docker-php-ext-enable gd \
    && docker-php-ext-enable zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN sh -c "$(wget -O- https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"
RUN a2enmod rewrite

COPY . .
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD chsh -s $(which zsh) \
    && zsh \
    && composer install \
    && touch storage/logs/resources/console-web.log \
    && touch storage/logs/resources/login.log \
    && touch storage/logs/resources/user-registration.log \
    && touch storage/logs/resources/available-urls.log \
    && touch storage/logs/server/web-server.log \
    && touch storage/logs/sockets/socket.log \
    && touch storage/logs/supervisord/supervisord.log \
    && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
