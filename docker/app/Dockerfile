FROM php:8.3-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    bash

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN git config --global --add safe.directory /app

# Set working directory
WORKDIR /app

# Configure PHP
RUN echo "memory_limit = 256M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Keep container running
CMD ["tail", "-f", "/dev/null"]
