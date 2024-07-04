#!/bin/bash

# Load variables from .env file
set -o allexport
source .dockerenv
set +o allexport

# Variables for the connection to MySQL
USER_TO_GRANT="diego"
USER_PASSWORD="123123"

# Check if the user exists in MySQL
user_exists=$(mysql --user="$MYSQL_ROOT_USER" --password="$MYSQL_ROOT_PASSWORD" --batch --skip-column-names -e \
    "SELECT COUNT(*) FROM mysql.user WHERE user = '$USER_TO_GRANT' AND host = '%';")

# Check if the user already exists
if [ "$user_exists" -eq 0 ]; then
    # User does not exist, create the user
    mysql --user="$MYSQL_ROOT_USER" --password="$MYSQL_ROOT_PASSWORD" <<EOF
    CREATE USER '$USER_TO_GRANT'@'%' IDENTIFIED BY '$USER_PASSWORD';
    GRANT ALL PRIVILEGES ON *.* TO '$USER_TO_GRANT'@'%' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
EOF
    echo "User '$USER_TO_GRANT' created and granted all privileges."
else
    # User already exists, grant privileges only
    mysql --user="$MYSQL_ROOT_USER" --password="$MYSQL_ROOT_PASSWORD" <<EOF
    GRANT ALL PRIVILEGES ON *.* TO '$USER_TO_GRANT'@'%' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
EOF
    echo "User '$USER_TO_GRANT' already exists. Granted all privileges."
fi
