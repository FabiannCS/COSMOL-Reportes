FROM php:7.3-apache

# Configurar repositorios de Debian Bullseye por HTTPS
RUN echo 'deb https://deb.debian.org/debian bullseye main' > /etc/apt/sources.list \
    && echo 'deb https://security.debian.org/debian-security bullseye-security main' >> /etc/apt/sources.list

# Instalar libpq-dev para compilar pdo_pgsql
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP: PDO + PostgreSQL + OPcache
RUN docker-php-ext-install pdo pdo_pgsql opcache

# Configuración de OPcache para desarrollo de alto rendimiento
RUN echo '[opcache]\n\
opcache.enable=1\n\
opcache.enable_cli=0\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=4000\n\
opcache.validate_timestamps=1\n\
opcache.revalidate_freq=2\n\
opcache.fast_shutdown=1' > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Módulos Apache: rewrite, deflate (compresión), expires (caché), headers
RUN a2enmod rewrite deflate expires headers \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# DocumentRoot → public/ (app/ queda inaccesible por URL)
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/default-ssl.conf 2>/dev/null || true

# AllowOverride All en public/
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/cosmol.conf \
    && a2enconf cosmol

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
