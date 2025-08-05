#!/bin/bash

# Script para probar el sistema de recordatorios
echo "Ejecutando comando de recordatorios..."
cd /home/usuario/Escritorio/Aircury/SummerOfCode/Pet-finder/projects/web

# Ejecutar el comando de recordatorios
php bin/console app:send-reminders

echo "Comando ejecutado. Revisa los logs para ver si se enviaron emails."
