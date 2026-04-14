# Lancer le projet avec Docker

Ce guide explique comment demarrer Docker, lancer l'application Symfony et acceder aux services dans le navigateur.

## 1. Demarrer Docker

Sur Mac, commence par ouvrir **Docker Desktop** et attends que Docker soit bien demarre.

Tu peux verifier que tout est pret avec :

```bash
docker compose ps
```

## 2. Aller dans le projet

Dans un terminal :

```bash
cd /Users/king/Documents/FAC/Portfolio_Synfony
```

## 3. Premier lancement

Si c'est la premiere fois que tu lances le projet avec Docker, execute :

```bash
docker compose run --rm web composer install
docker compose up --build -d
docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction
```

Si tu veux charger des donnees de demo en plus :

```bash
docker compose exec web php bin/console doctrine:fixtures:load --no-interaction
```

## 4. Lancer le site ensuite

Une fois l'installation initiale faite, pour redemarrer le projet plus tard il suffit en general de faire :

```bash
docker compose up -d
```

## 5. Acceder aux services

Quand les conteneurs tournent, ouvre dans ton navigateur :

- application Symfony : `http://localhost:8000`
- boite mail de test Mailpit : `http://localhost:8025`
- PostgreSQL : `localhost:5432`

## 6. Verifier que tout tourne

Pour voir l'etat des conteneurs :

```bash
docker compose ps
```

Pour voir les logs si quelque chose ne marche pas :

```bash
docker compose logs -f
```

## 7. Arreter le projet

Pour arreter les conteneurs :

```bash
docker compose down
```

## Resume rapide

Commande complete pour un premier lancement :

```bash
cd /Users/king/Documents/FAC/Portfolio_Synfony
docker compose run --rm web composer install
docker compose up --build -d
docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction
```

Puis ouvre `http://localhost:8000`.
