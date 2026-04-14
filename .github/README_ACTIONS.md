# GitHub Actions

Ce dossier contient les workflows de validation continue du projet. Leur objectif est de detecter rapidement les regressions avant une fusion dans `main`.

## Workflows en place

### `tests.yml`

Nom du workflow : `Tests`

Declencheurs :

- `push` vers `main` ;
- `pull_request` vers `main`.

Etapes principales :

- recuperation du depot ;
- installation de PHP 8.3 et des extensions necessaires ;
- validation de `composer.json` ;
- installation des dependances Composer ;
- demarrage d'un service PostgreSQL 16 ;
- generation de `.env.test.local` ;
- execution des migrations Doctrine en environnement `test` ;
- chargement des fixtures ;
- lancement de `php bin/phpunit --testdox`.

Variables preparees en CI :

- `DATABASE_URL` pointant vers la base PostgreSQL du job ;
- `MAILER_DSN=null://null` ;
- `MAILER_FROM_EMAIL` et `MAILER_FROM_NAME` ;
- `ADMIN_EMAIL`, `ADMIN_FIRSTNAME`, `ADMIN_LASTNAME` et `ADMIN_PASSWORD` pour les fixtures admin.

### `quick-check.yml`

Nom du workflow : `Quick Checks`

Declencheurs :

- `push` vers `main` ;
- `pull_request` vers `main`.

Etapes principales :

- recuperation du depot ;
- installation de PHP 8.3 ;
- installation des dependances Composer ;
- `composer validate --strict` ;
- verification de la syntaxe PHP sur `src/` et `tests/` ;
- affichage des prerequis PHP utilises par le job.

## Reproduire la CI en local

Par defaut, l'environnement `test` du projet peut utiliser `sqlite:///%kernel.project_dir%/var/test.db`. Pour coller davantage a la CI, definissez `TEST_DATABASE_URL` vers PostgreSQL avant d'executer les commandes ci-dessous.

Pour reproduire le workflow `Tests` au plus proche :

```bash
composer install
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --no-interaction
php bin/phpunit --testdox
```

Pour reproduire seulement `Quick Checks` :

```bash
composer validate --strict
find src tests -name "*.php" -exec php -l {} \;
```

## Consulter les resultats

- ouvrez l'onglet `Actions` du depot ;
- choisissez le workflow `Tests` ou `Quick Checks` ;
- inspectez l'etape en echec pour identifier si le probleme vient de Composer, de la base de test, des fixtures ou de PHPUnit.

## Quand modifier ces fichiers

Mettez a jour les workflows si vous changez :

- la version de PHP supportee ;
- la configuration de la base de test ;
- les commandes a executer avant les tests ;
- les outils de qualite utilises par le projet.
