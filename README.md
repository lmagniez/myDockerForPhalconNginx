# 0. About the project

The official project phalcon-compose allowing to create easily container with phalcon is no longer maintained. 
I tried in this project to create a basic docker container containing a nginx server with phalcon installed.

The project use several components:
- Web(nginx) (Docker image: nginx:latest)      
- PHP:7.1 (Docker image: php:7.1-fpm)
- Database:mariadb (Docker image: mariadb)
- PhpMyAdmin (Docker image: phpmyadmin/phpmyadmin)



# 1. How to run

## 1.1 Run docker
```
docker-compose up
```
phpmyadmin component port: 8090
web component port: 8080

Accès en bash
```
docker exec -it <ContainerId> bash
```

the docker-compose file will:
1. put the /code folder, site.conf and nginx.conf in the web component
2. download and compile the phalcon source code in the php component

## 1.2 Connect to db from host
```
mysql -h localhost -P 3306 --protocol=tcp -u root -p
```
db login are available and can be change in docker-compose.yml.

## 1.3 Access to the phalcon app

Url to access the app:
localhost:8080/

# 2. How to configure

## 2.1 Change database root identifiers
in the /docker-compose.yml file, change the environment values in both db and php components
```
  php:
    [...]
    environment:
        DB_HOST: db
        DB_DATABASE: database
        DB_USER: admin
        DB_PASSWORD: test
    [...]

  db:
    [...]
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_USER: admin
      MYSQL_PASSWORD: test
      MYSQL_DATABASE: database
    [...]
```

## 2.2 Insert database script

Script are added automatically to the database.
When executing docker-compose, the db component will execute the script in the docker/maria_db/db_init/1_ddl.sql file.

## 2.3 Insert phalcon app
- In the /code folder, insert your app folder. (A sample project is already included)
- In the /site.conf file, change every "sample-project" with your project name.
```
server {
    listen   80;
    server_name localhost;
    [...]
    root /code/sample-project/public;
    [...]
    
    location / {
	    root /code/sample-project/public;
      [...]
    }
    [...]
}
```
