#!/bin/sh

# Crear el directorio si no existe
mkdir -p /var/www/html/storage/logs

# Cambiar permisos del directorio storage y sus subdirectorios
chmod -R 775 /var/www/html/storage

# Cambiar permisos del archivo supervisord.log
chmod 777 /var/www/html/supervisord.log

# Ejecutar el comando principal del contenedor
exec "$@"
