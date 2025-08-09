FROM php:8.2-fpm

# Instalar Nginx, extensiones necesarias y utilidades
RUN apt-get update && apt-get install -y \
    nginx git unzip libicu-dev libzip-dev zlib1g-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Cambiar PHP-FPM para que escuche en TCP 9000 en lugar de socket Unix
RUN sed -i 's|listen = /var/run/php/php8.2-fpm.sock|listen = 9000|' /usr/local/etc/php-fpm.d/www.conf

# Configurar Nginx (escucha en puerto 80)
RUN echo 'server { \
    listen 80; \
    server_name _; \
    root /var/www/html/public; \
    index index.php index.html; \
    location / { try_files $uri /index.php$is_args$args; } \
    location ~ \.php$ { \
        include fastcgi_params; \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        fastcgi_param HTTPS off; \
    } \
    location ~* \.(jpg|jpeg|png|gif|css|js|ico|svg)$ { \
        expires 1y; access_log off; \
    } \
}' > /etc/nginx/sites-available/default

# Copiar proyecto Symfony al directorio de trabajo
WORKDIR /var/www/html
COPY web/ /var/www/html/

# Ajustar permisos para www-data
RUN chown -R www-data:www-data /var/www/html

# Copiar script de arranque que inicie php-fpm y nginx juntos
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Exponer puerto 80 para Elastic Beanstalk o Docker
EXPOSE 80

# Comando por defecto para arrancar PHP-FPM y Nginx
CMD ["/usr/local/bin/docker-entrypoint.sh"]
