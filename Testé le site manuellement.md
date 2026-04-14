Oui: terminal pour démarrer, navigateur pour regarder.

**Voir le site**
Le plus simple ici, si ta base PostgreSQL locale tourne déjà sur `localhost:5432`, c’est :

```bash
cd /Users/king/Documents/FAC/Portfolio_Synfony
symfony server:start
```

Puis tu ouvres `http://127.0.0.1:8000` dans ton navigateur.

Si c’est le tout premier lancement, fais avant :

```bash
composer install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
```

Si tu n’as pas PostgreSQL en local, utilise Docker. Docker est installé, mais il n’est pas démarré pour l’instant, donc il faut d’abord lancer Docker Desktop, puis :

```bash
cd /Users/king/Documents/FAC/Portfolio_Synfony
docker compose run --rm web composer install
docker compose up --build -d
docker compose exec web php bin/console doctrine:migrations:migrate
```

Ensuite ouvre `http://localhost:8000`. Pour les mails de test, ouvre `http://localhost:8025`.

**Tests auto**
Si tu veux lancer les tests automatiques, ce n’est pas le navigateur mais :

```bash
php bin/phpunit
```

Ici, cette commande marche sans Docker, mais elle a actuellement 2 tests en échec dans le projet. Pour arrêter le site ensuite : `symfony server:stop` en local, ou `docker compose down` avec Docker.

Si tu veux aller au plus simple maintenant : essaie d’abord `symfony server:start`; si tu as une erreur de base de données, passe direct à Docker.