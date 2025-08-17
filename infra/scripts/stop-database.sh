#!/bin/bash

echo "Stopping and removing database container..."
docker stop pet-finder-mysql 2>/dev/null || true
docker rm pet-finder-mysql 2>/dev/null || true
echo "✅ Database container removed!"
