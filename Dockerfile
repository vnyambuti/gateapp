FROM php:8.4-fpm

# ---- System packages ----
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    git \
    nginx \
    supervisor \
    gettext-base \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    gnupg2 \
    apt-transport-https \
    zip \
    && rm -rf /var/lib/apt/lists/*

# ---- Microsoft ODBC Driver + sqlsrv/pdo_sqlsrv extensions ----
RUN curl https://packages.microsoft.com/keys/microsoft.asc | tee /etc/apt/trusted.gpg.d/microsoft.asc \
    && curl https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# ---- Node.js (needed to build Vite/Tailwind assets, which import from vendor/filament) ----
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# ---- PHP extensions ----
RUN docker-php-ext-install pdo mbstring exif pcntl bcmath gd zip intl

# ---- Composer ----
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# vendor/ must exist BEFORE the frontend build, since theme.css imports from
# vendor/filament/filament/resources/css/theme.css
RUN composer install --optimize-autoloader --no-dev --no-interaction

RUN npm ci && npm run build

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# ---- Nginx + Supervisor config ----
COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Render injects $PORT at runtime — nginx.conf is generated from the template on container start
EXPOSE 10000

CMD ["start.sh"]
