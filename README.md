# Portfolio Symfony

Application portfolio developpee avec Symfony 7.3. Le projet combine un site vitrine public, un espace utilisateur securise et un back-office pour administrer les projets affiches sur la page d'accueil.

## Ce que fait l'application

### Partie publique

- page d'accueil avec presentation, projets et contact ;
- fiches projet publiques ;
- pages legales et bandeau cookies.

### Partie utilisateur

- inscription ;
- verification d'email par code a 6 chiffres ;
- connexion et deconnexion ;
- reinitialisation du mot de passe par email ;
- gestion du profil : informations, mot de passe et suppression du compte.

### Partie administration

- acces reserve aux comptes `ROLE_ADMIN` ;
- creation, modification, suppression et reordonnancement des projets ;
- gestion des images de banniere et de galerie ;
- commandes Symfony pour creer un administrateur, suivre l'etat des utilisateurs et lancer les nettoyages.

## Stack technique

- PHP 8.2+ ;
- Symfony 7.3 ;
- Doctrine ORM + Doctrine Migrations ;
- PostgreSQL ;
- Twig, Bootstrap 5 et Font Awesome ;
- AssetMapper / Importmap, Stimulus et Symfony UX Turbo ;
- Symfony Mailer ;
- PHPUnit ;
- Docker Compose avec PostgreSQL et Mailpit.

## Arborescence utile

```text
.
|- assets/                # CSS, JS et controllers Stimulus
|- config/                # configuration Symfony, Doctrine, Security et Mailer
|- docs/                  # documentation complementaire
|- migrations/            # migrations Doctrine
|- public/                # point d'entree web et fichiers publics
|- src/
|  |- Command/            # commandes Symfony personnalisees
|  |- Controller/         # controleurs HTTP
|  |- DataFixtures/       # fixtures de developpement et de test
|  |- Entity/             # entites Doctrine
|  |- Form/               # formulaires Symfony
|  |- Repository/         # acces aux donnees
|  |- Service/            # logique metier
|- templates/             # vues Twig
|- tests/                 # tests fonctionnels et unitaires
```

Les medias uploades par l'application sont stockes dans `public/uploads/images`.

## Installation en local

### 1. Prerequis

- PHP 8.2 ou plus ;
- Composer ;
- PostgreSQL ;
- Symfony CLI (optionnel, mais pratique pour `symfony server:start`) ;
- un `MAILER_DSN` valide ou un outil de capture d'emails comme Mailpit.

### 2. Installer les dependances

```bash
composer install
```

### 3. Creer `.env.local`

Exemple minimal :

```dotenv
APP_SECRET=change-me
DATABASE_URL="postgresql://symfony:symfony@127.0.0.1:5432/portfolio?serverVersion=16&charset=utf8"
MAILER_DSN="smtp://127.0.0.1:1025"
MAILER_FROM_EMAIL=app@example.com
MAILER_FROM_NAME="Portfolio"
ADMIN_EMAIL=admin@example.com
ADMIN_FIRSTNAME=Admin
ADMIN_LASTNAME=Portfolio
ADMIN_PASSWORD=ChangeMe123!
```

Ce fichier reste local a votre machine et ne doit pas etre versionne.

### 4. Initialiser la base

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
```

En developpement, vous pouvez aussi charger les fixtures :

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

Attention : cette commande reinitialise les donnees de developpement.

### 5. Creer ou recreer l'administrateur

```bash
php bin/console app:recreate-admin
```

La commande lit les variables `ADMIN_*` depuis `.env.local`.

### 6. Lancer l'application

Avec Symfony CLI :

```bash
symfony server:start
```

L'application est ensuite disponible sur `http://127.0.0.1:8000`.

## Lancement avec Docker

Le depot contient un `Dockerfile` et un `docker-compose.yml` pour un environnement de developpement complet avec PostgreSQL et Mailpit.

```bash
docker compose run --rm web composer install
docker compose up --build -d
docker compose exec web php bin/console doctrine:migrations:migrate --no-interaction
```

En option :

```bash
docker compose exec web php bin/console doctrine:fixtures:load --no-interaction
```

Services exposes :

- application : `http://localhost:8000`
- Mailpit : `http://localhost:8025`
- PostgreSQL : `localhost:5432`

## Tests et CI

Tests locaux :

```bash
php bin/phpunit
```

Par defaut, l'environnement `test` peut utiliser la base SQLite `var/test.db`. Pour se rapprocher du workflow CI, vous pouvez definir `TEST_DATABASE_URL` vers une base PostgreSQL dediee.

Pour executer uniquement un fichier :

```bash
php bin/phpunit tests/Controller/ResetPasswordEmailTest.php --testdox
```

Le depot inclut deja deux workflows GitHub Actions :

- `Tests` : prepare PostgreSQL, applique les migrations, charge les fixtures et execute PHPUnit ;
- `Quick Checks` : valide `composer.json` et la syntaxe PHP.

Ces workflows se lancent sur :

- les `push` vers `main` ;
- les `pull_request` vers `main`.

## Commandes utiles

```bash
php bin/console app:create-user email@example.com MotDePasse123! --admin
php bin/console app:recreate-admin
php bin/console app:test-email email@example.com
php bin/console app:user-stats
php bin/console app:cleanup-expired-codes
php bin/console app:cleanup-unverified-users --dry-run
php bin/console app:scheduled-cleanup --report
php bin/phpunit
```

## Documentation complementaire

- [Nettoyage automatique](docs/CLEANUP_DOCUMENTATION.md)
- [Structure de navigation](docs/NAVIGATION_STRUCTURE.md)
- [Reference technique GitHub Actions](.github/README_ACTIONS.md)
- [Guide pratique GitHub Actions](GITHUB_ACTIONS_GUIDE.md)
- [Tests de reinitialisation de mot de passe](TESTS_EMAIL_RESET_PASSWORD.md)

## Points d'attention

- `doctrine:fixtures:load` est utile en dev et en test, mais reinitialise les donnees ;
- les uploads sont stockes localement dans `public/uploads/images` ;
- la commande `app:recreate-admin` depend des variables `ADMIN_*`.
