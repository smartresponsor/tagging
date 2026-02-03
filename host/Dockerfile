# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
FROM php:8.2-cli

RUN apt-get update && apt-get install -y --no-install-recommends       libpq-dev curl ca-certificates &&     docker-php-ext-install pdo pdo_pgsql &&     rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY run.sh /app/run.sh
RUN chmod +x /app/run.sh

EXPOSE 8080
CMD ["/app/run.sh"]
