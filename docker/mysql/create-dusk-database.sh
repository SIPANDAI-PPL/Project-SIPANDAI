#!/usr/bin/env bash

# Database khusus test Dusk. Data hasil test disimpan di sini dan tidak dikosongkan,
# terpisah dari database aplikasi dan database "testing" milik PHPUnit.
mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS sipandai_dusk;
    GRANT ALL PRIVILEGES ON \`sipandai_dusk%\`.* TO '$MYSQL_USER'@'%';
EOSQL
