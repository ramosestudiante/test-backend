<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://blog.petehouston.com/wp-content/uploads/2017/11/laravel-nginx.jpg" width="400" alt="Laravel Logo"></a></p>
<br/>


# Test Solution
Solución para el desafío de evaluación para desarrollador PHP/Laravel Vue.

## Autor
Diego Ramos Rios diegoe.ramosrios@gmail.com

## Technologies
- PHP/Laravel
- Nginx
- Docker
- Docker-compose
- Mysql

## Installation

### Environment variables

Copy `.env.example` to `.env` and set all values.

- `APP_ENV`: Environment. Values `local`.
- `PORT`: The application port exposed.

### Database environment:

- `DB_USERNAME`: The name of the user of database.
- `DB_PASSWORD`: The password of the user of database.
- `DB_DATABASE`: The name of the database.
- `DB_HOST`: Database host.
- `DB_PORT`: Database port.
- `DB_CONNECTION`: The Mysql type database.

### Docker configuration

- `DOCKER_DB_PORT`= The port of docker database container
- `DOCKER_APP_PORT`= The application port exposed in docker container.

### Documentation
The endpoint descriptions are defined with swagger in the [/api/docs](http://127.0.0.1:8000/api/docs) local or with docker [/api/docs](http://localhost:8080/api/docs) 

## Run the app

##### If port 3306 is being used:

### 🏗️ Run with Docker in local

## Windows
### Using Makefile on Windows
Using Makefile on Windows
It should be remembered that if you are on Windows the makefile file will work by installing the following tools

- MSYS2
- Cygwin
- MinGW
- WSL
- Git Bash

#### The best option is to go to the makefile file and copy the codes of each definition

## Linux / Ubuntu
Run the command to build docker images:

```
$ make run-local
```
### Migrations
To run migrations with:

```
$ make migrations
```
### Seeders
To run migrations with:
```
$ make seeders
```

## Tests
To run the tests you must execute:

```
$ make test
```
#### The testing tool used was PestPHP for its simplicity in describing the tests.