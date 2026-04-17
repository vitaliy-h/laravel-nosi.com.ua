FROM php:8.3-fpm
LABEL maintainer="osokol0444@gmail.com"

ARG HOST_UID=1000

# Installing dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    default-mysql-client \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libmagickwand-dev \
    libzip-dev \
    libonig-dev \
    locales \
    zip \
    supervisor \
    jpegoptim optipng pngquant gifsicle \
    && pecl install imagick

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installing extensions
RUN docker-php-ext-configure gd --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql mysqli mbstring zip exif pcntl bcmath opcache gd
RUN docker-php-ext-enable imagick
RUN docker-php-ext-install intl


COPY ./php.ini /usr/local/etc/php/

# Setting locales
RUN echo fr_FR.UTF-8 UTF-8 > /etc/locale.gen && locale-gen

# setup composer
RUN set -x && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

RUN usermod -u ${HOST_UID} www-data && groupmod -g ${HOST_UID} www-data && chsh -s /bin/bash www-data

# Setup cron
ADD ./crontab /etc/cron.d/cron-jobs
RUN chmod 0644 /etc/cron.d/cron-jobs && touch /var/log/cron.log

# setup supervisor
RUN mkdir -p /var/log/supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord"]
