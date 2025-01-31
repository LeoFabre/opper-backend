# 🚀 Run the project

```bash
docker-compose up -d
```

## ⚡ *When running for the first time, please wait for the composer install and DB hydration process : **it takes about 2 minutes to complete**.*

- Once done installing and starting the containers, the API is available at this base URL : <http://localhost:8080>

- ❤️ For your comfort, a Swagger is available at this URL : <http://localhost:8080/api/doc>

- There is also a phpMyAdmin that you can use to inspect DB content _**(id: symfony, password: symfony)**_ : <http://localhost:8081> 

# 🔎 Running Unit tests

If not already running, run the project with :  
```bash
docker-compose up -d
```
Then, run the unit tests with the following command :
```bash
docker exec -it symfony_app php vendor/bin/phpunit
```

Enjoy !
