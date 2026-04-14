# Portfolio Symfony

Application web de portfolio developpee avec Symfony 7.3. Le site permet de presenter des projets publiquement, de gerer un espace utilisateur avec authentification, et de disposer d'un back-office simple pour administrer les projets affiches sur la page d'accueil.

## Apercu du projet

Ce projet combine une vitrine publique et une partie applicative plus complete :

- page d'accueil avec banniere, presentation, liste des projets et section contact ;
- fiches detaillees publiques pour chaque projet ;
- inscription utilisateur avec verification d'email par code a 6 chiffres ;
- connexion, deconnexion et reinitialisation de mot de passe par email ;
- espace profil avec modification des informations, changement de mot de passe et suppression du compte ;
- espace administrateur pour creer, modifier, supprimer et reordonner les projets ;
- upload d'images pour les bannieres et galleries de projets ;
- pages legales et bandeau cookies ;
- commandes de maintenance pour nettoyer les comptes non verifies et les codes expires.

## Stack technique

- PHP 8.2
- Symfony 7.3
- Doctrine ORM + Doctrine Migrations
- PostgreSQL
- Twig
- Bootstrap 5 + Font Awesome
- AssetMapper / Importmap + Stimulus
- Symfony Mailer
- PHPUnit
- Docker + Docker Compose

## Fonctionnalites principales

### Partie publique

- affichage des projets tries par position ;
- navigation unifiee avec sections `A propos`, `Projets` et `Contact` ;
- consultation publique d'un projet via une page detaillee ;
- pages `Mentions legales`, `CGU` et `Politique de confidentialite`.

### Partie utilisateur

- creation de compte ;
- verification de l'adresse email par code temporaire ;
- connexion securisee avec formulaire Symfony ;
- reinitialisation du mot de passe ;
- gestion du profil utilisateur.

### Partie administration

- acces reserve aux comptes `ROLE_ADMIN` ;
- creation de projets avec titre, descriptions, technologies, lien et images ;
- suppression des fichiers uploades lors de la suppression d'un projet ;
- reorganisation de l'ordre d'affichage des projets ;
- commandes utilitaires pour recreer un compte admin et suivre l'etat des utilisateurs.

## Structure du projet

```text
.
|- assets/                # JS, CSS, controllers Stimulus
|- config/                # configuration Symfony, Doctrine, Security, Mailer
|- docs/                  # documentation complementaire
|- migrations/            # migrations Doctrine
|- public/                # point d'entree web et fichiers publics
|- src/
|  |- Command/            # commandes Symfony personnalisees
|  |- Controller/         # controleurs HTTP
|  |- Entity/             # entites Doctrine
|  |- Form/               # formulaires Symfony
|  |- Repository/         # acces aux donnees
|  |- Service/            # logique metier
|- templates/             # vues Twig
|- tests/                 # tests fonctionnels et unitaires
```

Le dossier d'upload utilise par l'application est `public/uploads/images`.

## Installation en local

### 1. Prerequis

- PHP 8.2+
- Composer
- PostgreSQL 16 recommande
- un serveur mail de dev ou un `MAILER_DSN` valide

### 2. Installation

```bash
composer install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
```

Optionnel en developpement pour charger les fixtures :

```bash
php bin/console doctrine:fixtures:load
```

### 3. Configuration d'environnement

Renseigner ou adapter les variables dans `.env.local` :

- `APP_SECRET`
- `DATABASE_URL`
- `MAILER_DSN`
- `MAILER_FROM_EMAIL`
- `MAILER_FROM_NAME`
- `ADMIN_EMAIL`
- `ADMIN_FIRSTNAME`
- `ADMIN_LASTNAME`
- `ADMIN_PASSWORD`

### 4. Lancer le projet

Avec Symfony CLI :

```bash
symfony server:start
```

Le site sera accessible sur `http://127.0.0.1:8000` selon votre configuration locale.

## Lancement avec Docker

Le projet contient un `Dockerfile` et un `docker-compose.yml` prets pour un environnement de developpement avec PostgreSQL et Mailpit.

```bash
docker compose run --rm web composer install
docker compose up --build -d
docker compose exec web php bin/console doctrine:migrations:migrate
```

Optionnel en developpement pour charger les fixtures :

```bash
docker compose exec web php bin/console doctrine:fixtures:load
```

Services disponibles :

- application web : `http://localhost:8000`
- Mailpit : `http://localhost:8025`
- PostgreSQL : port `5432`

## Compte administrateur

La methode recommandee est :

```bash
php bin/console app:recreate-admin
```

La commande `app:recreate-admin` lit les informations `ADMIN_*` depuis `.env.local`.

Les fixtures peuvent aussi creer un compte administrateur, mais `php bin/console doctrine:fixtures:load` est a reserver a un environnement de developpement car cette commande recharge les donnees.

## Commandes utiles

```bash
php bin/console app:create-user email@example.com MotDePasse123! --admin
php bin/console app:recreate-admin
php bin/console app:user-stats
php bin/console app:cleanup-expired-codes
php bin/console app:cleanup-unverified-users --dry-run
php bin/console app:scheduled-cleanup --report
php bin/phpunit
```

## Tests

Les tests couvrent notamment :

- l'inscription et la verification email ;
- l'authentification admin ;
- l'acces aux routes protegees ;
- la reinitialisation de mot de passe ;
- certains services metier.

Lancement :

```bash
php bin/phpunit
```

## Documentation complementaire

- [Nettoyage automatique](docs/CLEANUP_DOCUMENTATION.md)
- [Structure de navigation](docs/NAVIGATION_STRUCTURE.md)
- [Tests email reset password](TESTS_EMAIL_RESET_PASSWORD.md)
- [Guide GitHub Actions](GITHUB_ACTIONS_GUIDE.md)

## Pistes d'amelioration

- ajouter un vrai workflow CI pour executer les tests automatiquement ;
- externaliser la gestion des medias avec un service dedie ;
- renforcer la documentation des routes et des conventions de nommage ;
- separer davantage le front public et le back-office si le projet grossit.
