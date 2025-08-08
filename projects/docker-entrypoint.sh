#!/bin/bash
set -e

# Arrancar PHP-FPM en background
php-fpm &

# Arrancar Nginx en primer plano
nginx -g "daemon off;"
