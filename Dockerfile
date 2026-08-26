FROM php:7.3-apache

# Configurar repositorios de Debian Bullseye por HTTPS
RUN echo 'deb https://deb.debian.org/debian bullseye main' > /etc/apt/sources.list \
    && echo 'deb https://security.debian.org/debian-security bullseye-security main' >> /etc/apt/sources.list

# Instalar libpq-dev para compilar pdo_pgsql
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP: PDO + PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# mod_rewrite para .htaccess
RUN a2enmod rewrite

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
