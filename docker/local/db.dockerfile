# image MySQL
FROM mysql:8.0

# create plugins directory
RUN mkdir -p /usr/lib64/mysql/plugin/

# copy plugin mysql
# This is an authentication plugin.
COPY ./docker/local/my.cnf /usr/lib64/mysql/plugin/

# sets read and write permissions for the owner and read-only permissions for other users
RUN chmod 644 /usr/lib64/mysql/plugin/my.cnf

# run the init-db.sh script when creating the container, as the Docker entry point finds SQL scripts in this directory during its initialization and will automatically run them to initialize the database.
COPY ./docker/init-db.sh /docker-entrypoint-initdb.d/

# gives you execution permissions
RUN chmod +x /docker-entrypoint-initdb.d/init-db.sh
