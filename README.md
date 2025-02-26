<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://blog.petehouston.com/wp-content/uploads/2017/11/laravel-nginx.jpg" width="400" alt="Laravel Logo"></a></p>
<br/>


# Test Solution
Solution to the developer evaluation challenge. PHP/Laravel Vue.

## Autor
Diego Ramos Rios diegoe.ramosrios@gmail.com

## Technologies
- PHP/Laravel
- Nginx
- Docker
- Docker-compose
- Mysql

## Installation
### credentials to enter the application
- User: admin@example.com
- password: Password123!

### Environment variables

Copy `.env.example` to `.env` and set all values.

- `APP_ENV`: Environment. Values `local` or `development`.
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

### Create network
```
$ docker network create backend_test
```
### Create containers
```
$ docker compose -f ./docker/docker-compose.local.yml --project-directory ./ -p backend_api down
```
```
$ docker compose -f ./docker/docker-compose.local.yml --project-directory ./ -p backend_api build --no-cache
```
```
$ docker compose -f ./docker/docker-compose.local.yml --project-directory ./ -p backend_api up -d db app
```
### Migrations
```
$ php artisan migrate
```
### seeders
```
$ php artisan db:seed
```

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


## API REST
I opted to manually implement the REST API instead of using Laravel's apiResource method or the --api flag when generating controllers, this allows me to define grouping based on the auth.api and admin middleware to manage permissions

For Example --api flag
```
$ php artisan make:controller PostController --api

the code above generates this

$ Route::apiResource('posts', PostController::class);

```


