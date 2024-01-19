FROM php:8.3-fpm-bullseye AS base

WORKDIR /workspace

# timezone environment
ENV TZ=UTC \
  # locale
  LANG=en_US.UTF-8 \
  LANGUAGE=en_US:en \
  LC_ALL=en_US.UTF-8 \
  # composer environment
  COMPOSER_ALLOW_SUPERUSER=1 \
  COMPOSER_HOME=/composer

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

RUN <<EOF
  apt-get update
  apt-get -y install --no-install-recommends \
    locales \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev
  locale-gen en_US.UTF-8
  localedef -f UTF-8 -i en_US en_US.UTF-8

  composer config -g process-timeout 3600
  composer config -g repos.packagist composer https://packagist.org
EOF

FROM base AS development

RUN <<EOF
  apt-get -y install --no-install-recommends \
    default-mysql-client
  apt-get clean
  rm -rf /var/lib/apt/lists/*
EOF

COPY ./infra/docker/php/php.development.ini /usr/local/etc/php/php.ini
COPY ./config/zz-nolog.conf /usr/local/etc/php-fpm.d/zz-nolog.conf

FROM development AS development-xdebug

RUN <<EOF
  pecl install xdebug
  docker-php-ext-enable xdebug
EOF

COPY ./infra/docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

FROM base AS deploy

COPY ./infra/docker/php/php.deploy.ini /usr/local/etc/php/php.ini
COPY ./src /workspace

RUN <<EOF
  chmod -R 777 storage bootstrap/cache
  php artisan optimize:clear
  php artisan optimize
  apt-get clean
  rm -rf /var/lib/apt/lists/*
EOF

ARG GIT_COMMIT
ENV GIT_COMMIT=$GIT_COMMIT

# Install the PDO and utilities.
RUN docker-php-ext-install pdo_mysql pdo

CMD ["php", "artisan", "websockets:serve"]
