# Lancer le projet

```bash
docker-compose up -d
```

Penser a hydrater la DB en faisant :

```bash
docker exec -it symfony_app php bin/console doctrine:fixtures:load
```