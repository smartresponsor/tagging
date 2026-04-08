FROM php:8.4-cli-bookworm

ARG NODE_MAJOR=22

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    PLAYWRIGHT_BROWSERS_PATH=/ms-playwright

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        ca-certificates \
        chromium \
        chromium-driver \
        curl \
        git \
        gnupg \
        libasound2 \
        libatk-bridge2.0-0 \
        libatk1.0-0 \
        libcups2 \
        libdbus-1-3 \
        libdrm2 \
        libgbm1 \
        libgtk-3-0 \
        libicu-dev \
        libnss3 \
        libpq-dev \
        libsqlite3-dev \
        libu2f-udev \
        libzip-dev \
        libx11-xcb1 \
        libxcomposite1 \
        libxdamage1 \
        libxfixes3 \
        libxkbcommon0 \
        libxrandr2 \
        postgresql-client \
        unzip \
        xdg-utils \
        zip \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
        | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && NODE_SOURCE_LINE="deb [signed-by=/etc/apt/keyrings/nodesource.gpg]" \
    && NODE_SOURCE_LINE="$NODE_SOURCE_LINE https://deb.nodesource.com/node_${NODE_MAJOR}.x" \
    && NODE_SOURCE_LINE="$NODE_SOURCE_LINE nodistro main" \
    && printf "%s\n" "$NODE_SOURCE_LINE" > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-install intl pdo_pgsql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY deploy/docker/entrypoint.sh /usr/local/bin/tag-entrypoint

RUN chmod +x /usr/local/bin/tag-entrypoint

WORKDIR /app

ENTRYPOINT ["tag-entrypoint"]
