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
# Configure PHP-Extensions ---------------------------------------------------------------------------------------------
RUN pecl install ev redis xdebug \
    && docker-php-ext-install mbstring gd zip pdo pdo_mysql pdo_pgsql \
    && docker-php-ext-enable xdebug redis gd zip pdo_pgsql
# Configure Xdebug
RUN { \
      echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)"; \
      echo "xdebug.mode=develop,debug,coverage"; \
      echo "xdebug.start_with_request=yes"; \
      echo "xdebug.idekey=PHPSTORM"; \
      echo "xdebug.log=/tmp/xdebug.log"; \
      echo "xdebug.log_level=7"; \
      echo "xdebug.discover_client_host=false"; \
      echo "xdebug.client_host=host.docker.internal"; \
      echo "xdebug.client_port=9003"; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
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

