FROM php:8.2-fpm

# Instalar Nginx y extensiones necesarias para Symfony
RUN apt-get update && apt-get install -y \
    nginx git unzip libicu-dev libzip-dev zlib1g-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurar Nginx para Symfony (puerto 80, root en /var/www/html/public)
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

# Establecer directorio de trabajo y copiar el proyecto Symfony desde la carpeta web
WORKDIR /var/www/html
COPY web/ /var/www/html/

# Ajustar permisos para www-data
RUN chown -R www-data:www-data /var/www/html

# Copiar script para arrancar php-fpm y nginx juntos
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Comando para iniciar el contenedor (php-fpm en background + nginx en foreground)
CMD ["/usr/local/bin/docker-entrypoint.sh"]
