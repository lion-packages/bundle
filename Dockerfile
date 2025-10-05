FROM php:8.4-apache

ARG DEBIAN_FRONTEND=noninteractive
# ----------------------------------------------------------------------------------------------------------------------
USER root

# Add User -------------------------------------------------------------------------------------------------------------
RUN useradd -m lion && echo 'lion:lion' | chpasswd && usermod -aG sudo lion && usermod -s /bin/bash lion
# Dependencies ---------------------------------------------------------------------------------------------------------
RUN apt-get update -y \
    && apt-get upgrade -y \
    && apt-get install -y sudo nano zsh git curl wget unzip cron golang-go libpq-dev libpng-dev libzip-dev zlib1g-dev \
    && apt-get install -y libonig-dev libevent-dev \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update -y \
    && apt-get install -y wget lsb-release gnupg \
    && wget https://dev.mysql.com/get/mysql-apt-config_0.8.29-1_all.deb \
    && dpkg -i mysql-apt-config_0.8.29-1_all.deb \
    && sed -i 's/trixie/bookworm/g' /etc/apt/sources.list.d/mysql.list \
    && apt-get update -y \
    && apt-get install -y mysql-client \
    && rm -f mysql-apt-config_0.8.29-1_all.deb \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP-Extensions ---------------------------------------------------------------------------------------------
RUN pecl install ev redis xdebug \
    && docker-php-ext-install mbstring gd zip pdo pdo_mysql pdo_pgsql \
    && docker-php-ext-enable xdebug redis gd zip pdo_pgsql

# Configure Xdebug
RUN echo "xdebug.mode=develop,coverage,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=docker" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/dev/stdout" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# ----------------------------------------------------------------------------------------------------------------------
USER lion

SHELL ["/bin/bash", "--login", "-i", "-c"]

# Install nvm, Node.js and npm -----------------------------------------------------------------------------------------
ENV NVM_DIR="/home/lion/.nvm"
ENV PATH="$NVM_DIR/versions/node/v20/bin:$PATH"

RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash \
    && source /home/lion/.bashrc \
    && nvm install 20 \
    && npm install -g npm@11

# Install OhMyZsh ------------------------------------------------------------------------------------------------------
RUN sh -c "$(wget https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh -O -)"
# ----------------------------------------------------------------------------------------------------------------------
USER root

SHELL ["/bin/bash", "--login", "-c"]

# Install logo-ls ------------------------------------------------------------------------------------------------------
RUN ARCH=$(uname -m) && \
    if [ "$ARCH" = "x86_64" ]; then \
        wget https://github.com/Yash-Handa/logo-ls/releases/download/v1.3.7/logo-ls_amd64.deb; \
    elif [ "$ARCH" = "aarch64" ]; then \
        wget https://github.com/Yash-Handa/logo-ls/releases/download/v1.3.7/logo-ls_arm64.deb; \
    else \
        echo "Unsupported architecture: $ARCH" && exit 1; \
    fi && \
    dpkg -i logo-ls_*.deb && \
    rm logo-ls_*.deb && \
    curl https://raw.githubusercontent.com/UTFeight/logo-ls-modernized/master/INSTALL | bash

# Add configuration in .zshrc ------------------------------------------------------------------------------------------
RUN echo 'export NVM_DIR="$HOME/.nvm"' >> /home/lion/.zshrc \
    && echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> /home/lion/.zshrc \
    && echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> /home/lion/.zshrc \
    && echo 'alias ls="logo-ls"' >> /home/lion/.zshrc \
    && source /home/lion/.zshrc

# ----------------------------------------------------------------------------------------------------------------------
# Copy Data
COPY . .

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

