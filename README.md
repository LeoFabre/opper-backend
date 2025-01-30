# Lancer le projet

```bash
docker-compose up -d
```

Penser a hydrater la DB en faisant :

```bash
docker exec -it symfony_app php bin/console doctrine:fixtures:load
```

une fois lancé, le projet est accessible à l'adresse suivante : http://localhost:8080  
Pour plus de comfort, un swagger est disponible à l'adresse suivante : http://localhost:8080/api/doc

# TODO UNIT TESTS AAAAAAAAAA