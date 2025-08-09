FROM php:8.2-apache

# Instalar extensiones necesarias y Composer
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install zip pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar composer.json y composer.lock primero (para cache de Docker)
COPY composer.json composer.lock ./

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar el resto del proyecto
COPY . .

# Ejecutar composer install nuevamente por si hay archivos adicionales
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Limpiar cache de Symfony
RUN php bin/console cache:clear --env=prod --no-debug || echo "Cache clear failed, continuing..."

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/var || true

# Configurar Apache para Symfony
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Order Allow,Deny\n\
        Allow from All\n\
        FallbackResource /index.php\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

EXPOSE 80