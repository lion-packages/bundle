FROM php:8.4-apache

ARG DEBIAN_FRONTEND=noninteractive
# ----------------------------------------------------------------------------------------------------------------------
USER root

# Add User -------------------------------------------------------------------------------------------------------------
RUN useradd -m lion && echo 'lion:lion' | chpasswd && usermod -aG sudo lion && usermod -s /bin/bash lion
# Dependencies ---------------------------------------------------------------------------------------------------------
RUN apt-get update -y \
    && apt-get install -y sudo nano zsh git default-mysql-client curl wget unzip cron sendmail golang-go \
    && apt-get install -y libpq-dev libpng-dev libzip-dev zlib1g-dev libonig-dev libevent-dev libssl-dev \
    && sudo apt-get install -y ca-certificates \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Electron-Vite Dependencies
# RUN apt-get update -y \
    # && apt-get install -y libnss3 mesa-utils libgl1-mesa-glx mesa-utils-extra libx11-xcb1 libxcb-dri3-0 libxtst6 \
    # && apt-get install -y libasound2 libgtk-3-0 libcups2 libatk-bridge2.0 libatk1.0 libcanberra-gtk-module \
    # && apt-get install -y libcanberra-gtk3-module dbus libdbus-1-3 dbus-user-session \
    # && apt-get clean \
    # && rm -rf /var/lib/apt/lists/*

# Configure PHP-Extensions ---------------------------------------------------------------------------------------------
RUN pecl install ev redis xdebug \
    && docker-php-ext-install mbstring gd zip pdo pdo_mysql pdo_pgsql \
    && docker-php-ext-enable xdebug redis gd zip pdo_pgsql

# Configure Xdebug
RUN echo "xdebug.mode=develop,coverage,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_connect_back=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=docker" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/dev/stdout" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
# Apache config --------------------------------------------------------------------------------------------------------
RUN a2enmod rewrite ssl \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i "s|SSLEngine on|SSLEngine on\nSSLVerifyClient none|g" /etc/apache2/sites-available/default-ssl.conf \
    && sed -i "s|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g" /etc/apache2/sites-available/000-default.conf \
    && sed -i "s|<Directory /var/www/html>|<Directory /var/www/html/public>|g" /etc/apache2/apache2.conf \
    && sed -i "s|SSLProtocol all|SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1|g" /etc/apache2/mods-enabled/ssl.conf \
    && sed -i "s|SSLHonorCipherOrder on|SSLHonorCipherOrder on\nSSLCipherSuite HIGH:!aNULL:!MD5|g" /etc/apache2/mods-enabled/ssl.conf \
    && sed -i "s|www-data|lion|g" /etc/apache2/envvars \
    && openssl req -x509 -nodes -days 365 -newkey rsa:4096 \
        -keyout /etc/ssl/private/apache-selfsigned.key \
        -out /etc/ssl/certs/apache-selfsigned.crt \
        -subj "/C=US/ST=State/L=City/O=Lion-Packages/OU=Lion/CN=localhost/emailAddress=root@dev.com" \
    && cp /etc/ssl/certs/apache-selfsigned.crt /usr/local/share/ca-certificates/selfsigned.crt \
    && update-ca-certificates \
    && echo "\n\
<VirtualHost *:80> \n\
    ServerName localhost \n\
    DocumentRoot /var/www/html/public \n\
    SSLEngine on \n\
    SSLCertificateFile /etc/ssl/certs/apache-selfsigned.crt \n\
    SSLCertificateKeyFile /etc/ssl/private/apache-selfsigned.key \n\
    ErrorLog \${APACHE_LOG_DIR}/error.log \n\
    CustomLog \${APACHE_LOG_DIR}/access.log combined \n\
</VirtualHost> \n\
\n\
<VirtualHost *:443> \n\
    ServerName localhost \n\
    DocumentRoot /var/www/html/public \n\
    SSLEngine on \n\
    SSLCertificateFile /etc/ssl/certs/apache-selfsigned.crt \n\
    SSLCertificateKeyFile /etc/ssl/private/apache-selfsigned.key \n\
    ErrorLog \${APACHE_LOG_DIR}/error.log \n\
    CustomLog \${APACHE_LOG_DIR}/access.log combined \n\
</VirtualHost> \n\
    " > /etc/apache2/sites-available/default-ssl.conf \
    && a2ensite default-ssl
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
