FROM php:7.1-fpm

# PHP extensions, libs and tools
RUN apt-get update && apt-get install -y \
        git \
        iputils-ping \
        less \
        nano \
        vim \
        net-tools \
        ssh \
        unzip \
        zip \
		dos2unix \
        --no-install-recommends \
    && docker-php-ext-install -j$(nproc) pdo_mysql bcmath sockets \
    && docker-php-ext-enable pdo_mysql bcmath sockets \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy local.xml config
COPY local.xml /tmp/local.xml

# Copy shell script for setting up Bitbucket SSH key
COPY docker-entrypoint.sh /tmp
RUN chmod u+x /tmp/docker-entrypoint.sh

# Workaround for Windows to remove erroneous carriage returns
RUN dos2unix /tmp/docker-entrypoint.sh

# Install composer
RUN curl -s http://getcomposer.org/installer | php -- --version=1.10.20 \
    && mv composer.phar /usr/local/bin/composer

# Install nodejs
RUN curl -sL https://deb.nodesource.com/setup_10.x | bash - \
  && apt-get install -y nodejs \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

# Install useful NPM command line tools
RUN npm i -g gulp-cli

WORKDIR /var/www/

ENTRYPOINT ["/tmp/docker-entrypoint.sh"]
CMD ["php-fpm"]
