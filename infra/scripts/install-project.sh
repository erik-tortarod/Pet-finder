#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR/../../projects/web"

echo "Installing PHP dependencies..."
cd $PROJECT_DIR && composer install

echo "Installing Node.js dependencies..."
cd $PROJECT_DIR && npm install

echo "Building assets..."
cd $PROJECT_DIR && npm run build

echo "✅ Project installation complete!"
