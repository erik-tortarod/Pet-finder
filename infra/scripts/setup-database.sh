#!/bin/bash

DB_PORT=3308
DB_NAME=petfinder
DB_USER=petfinder
DB_PASSWORD=petfinder

echo "Setting up MySQL database..."
docker run -d \
    --name pet-finder-mysql \
    -p $DB_PORT:3306 \
    -e MYSQL_ROOT_PASSWORD=root \
    -e MYSQL_DATABASE=$DB_NAME \
    -e MYSQL_USER=$DB_USER \
    -e MYSQL_PASSWORD=$DB_PASSWORD \
    mysql:8.0

echo "Waiting for database to be ready..."
sleep 10

echo "✅ Database setup complete!"
echo "Database connection:"
echo "  Host: localhost"
echo "  Port: $DB_PORT"
echo "  Database: $DB_NAME"
echo "  User: $DB_USER"
echo "  Password: $DB_PASSWORD"
