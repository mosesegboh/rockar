FROM php:7.1-fpm

# Whether to intall xdebug as well
ARG XDEBUG=false

EXPOSE 9000

# PHP extensions

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libpng-dev libxpm-dev \
    libfreetype6-dev \
    libxml2-dev \
    libmcrypt-dev \
    nodejs \
    npm \
    libfontconfig \
    openssl

RUN docker-php-ext-configure gd \
    --with-gd \
    --with-webp-dir \
    --with-jpeg-dir \
    --with-png-dir \
    --with-zlib-dir \
    --with-xpm-dir \
    --enable-gd-native-ttf

RUN docker-php-ext-install gd

RUN docker-php-ext-install exif

RUN docker-php-ext-install mcrypt

RUN docker-php-ext-install soap

RUN if [ $XDEBUG = "true" ]; then \
        pecl install xdebug-2.9.0; \
        docker-php-ext-enable xdebug; \
    fi;

RUN apt-get update && apt-get install -y \
    iputils-ping \
    nano \
    vim \
    dos2unix \
    --no-install-recommends \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd soap bcmath sockets \
    && docker-php-ext-enable pdo_mysql gd exif mcrypt soap bcmath sockets \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Custom php config
COPY ./www.conf /usr/local/etc/php-fpm.d/
COPY ./php.ini /usr/local/etc/php/php.ini

# Xdebug config
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

CMD ["php-fpm"]