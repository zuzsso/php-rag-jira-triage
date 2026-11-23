FROM php:8.3-apache

# Install system dependencies and required PHP extensions

#RUN apt-get update && apt-get install -y \
#    git \
#    unzip \
#    libcurl4-openssl-dev \
#    libonig-dev \
#    && docker-php-ext-install pdo_mysql curl mbstring \
#    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libcurl4-openssl-dev \
    libonig-dev \
    libicu-dev \
    && docker-php-ext-install pdo_mysql curl mbstring intl \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure Xdebug 3 for Docker -> Host PhpStorm debugging
RUN { \
    echo "xdebug.mode=debug,coverage"; \
    echo "xdebug.start_with_request=yes"; \
    echo "xdebug.client_host=host.docker.internal"; \
    echo "xdebug.client_port=9003"; \
    echo "xdebug.idekey=PHPSTORM"; \
    echo "xdebug.log_level=0"; \
} > /usr/local/etc/php/conf.d/99-xdebug.ini

# Enable Apache mod_rewrite for clean routing
RUN a2enmod rewrite

# Point Apache DocumentRoot to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Allow root inside container to run git/composer on host-mounted volumes
RUN git config --global --add safe.directory /var/www/html

WORKDIR /var/www/html
