# Run the project

```bash
docker-compose up -d
```

*Please wait for the hydration process of the DB to finish, **it takes about 45 seconds**.*

- Once done starting the containers, the API is available at this base URL : http://localhost:8080  

- For your comfort, a Swagger is available at this URL : http://localhost:8080/api/doc

# Running Unit tests

If not already the case, run the project with :  
```bash
docker-compose up -d
```
Then, run the unit tests with the following command :
```bash
docker exec -it symfony_app php vendor/bin/phpunit
```