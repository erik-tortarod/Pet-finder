#!/bin/bash

# Instalaciones base
sudo apt update -y
sudo apt upgrade -y

# Instalar git
sudo apt install git -y

# Instalar Node.js y npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Instalar dependencias para agregar repositorios
sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common gnupg2

# Agregar la clave GPG del repositorio de PHP
curl -sSL https://packages.sury.org/php/apt.gpg | sudo gpg --dearmor -o /etc/apt/trusted.gpg.d/php.gpg

# Agregar el repositorio de PHP
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list

# Actualizar repositorios después de agregar PHP
sudo apt update -y

# Instalar PHP 8.3 y extensiones
sudo apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-intl php8.3-gd

# Instalar Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/tmp
sudo mv /tmp/composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Instalar docker
sudo apt-get update -y
sudo apt-get install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/debian/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/debian \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update -y
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Clonar repositorio
sudo git clone https://github.com/erik-tortarod/Pet-finder.git /tmp/mi_repositorio

# Configurar permisos del repositorio clonado
sudo chown -R $USER:$USER /tmp/mi_repositorio
sudo chmod -R 755 /tmp/mi_repositorio

# Mover el archivo .env subido por Terraform al directorio del proyecto
sudo mv /tmp/.env /tmp/mi_repositorio/projects/web/.env

cd /tmp/mi_repositorio/projects/web

composer install --no-dev --optimize-autoloader
composer install

npm install

npm run build

sudo docker build -t symfony-app .

sudo docker run -d -p 80:80 --name symfony-app symfony-app

# SSL + DNS
sudo apt install -y nginx
sudo mv /tmp/certificate.crt /etc/ssl/certs/
sudo mv /tmp/private.key /etc/ssl/private/
sudo mv /tmp/ca_bundle.crt /etc/ssl/certs/

# TODO: reemplazar por el dominio
cat <<EOL | sudo tee /etc/nginx/sites-available/default
server {
    listen 443 ssl;
    server_name justdocitauth.site;

    ssl_certificate /etc/ssl/certs/certificate.crt;
    ssl_certificate_key /etc/ssl/private/private.key;
    ssl_trusted_certificate /etc/ssl/certs/ca_bundle.crt;

    location / {
        proxy_pass http://127.0.0.1:80;
    }
}
EOL

sudo systemctl restart nginx
