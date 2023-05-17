FROM php:8.2-apache
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y sudo \
    && apt-get install -y nano \
    && apt-get install -y cron \
    && apt-get install -y sendmail libpng-dev \
    && apt-get install -y libzip-dev \
    && apt-get install -y zlib1g-dev \
    && apt-get install -y libonig-dev \
    && apt-get install -y supervisor \
    && apt-get install -y libevent-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mbstring
RUN docker-php-ext-install gd
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install zip

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN a2enmod rewrite

CMD composer install
CMD php lion serve --host 0.0.0.0 --port 8000
EXPOSE 8000
