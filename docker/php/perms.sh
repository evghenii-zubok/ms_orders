#!/usr/bin/env bash

if [[ -d "/var/www/deploy/src" ]]
then
  echo "Folder exists"
  # riscriviamo i permessi dei file
  chown -R 1000:www-data /var/www/deploy/src/public
  chown -R 1000:www-data /var/www/deploy/src/storage
  chmod -R 775 /var/www/deploy/src/storage
fi


