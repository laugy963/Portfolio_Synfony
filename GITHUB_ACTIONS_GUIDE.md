# Guide GitHub Actions

Ce guide explique ou trouver les executions automatiques et comment verifier rapidement que la CI du projet fonctionne.

## Quand les workflows se declenchent

Les workflows du depot se lancent automatiquement :

- a chaque `push` vers `main` ;
- a chaque `pull_request` vers `main`.

## Les deux workflows a surveiller

### `Tests`

Ce workflow :

- prepare une base PostgreSQL de test ;
- applique les migrations Doctrine ;
- charge les fixtures ;
- execute `php bin/phpunit --testdox`.

### `Quick Checks`

Ce workflow :

- installe les dependances ;
- valide `composer.json` ;
- verifie la syntaxe PHP dans `src/` et `tests/`.

## Consulter une execution

1. Ouvrez votre depot GitHub.
2. Cliquez sur l'onglet `Actions`.
3. Selectionnez le dernier run de `Tests` ou de `Quick Checks`.
4. Ouvrez le job voulu pour consulter les logs et l'etape exacte en echec.

## Reproduire la CI en local

Workflow `Tests` :

```bash
composer install
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --no-interaction
php bin/phpunit --testdox
```

Par defaut, l'environnement `test` peut s'appuyer sur `var/test.db`. Si vous voulez vous rapprocher du workflow CI, definissez `TEST_DATABASE_URL` vers PostgreSQL avant ces commandes.

Workflow `Quick Checks` :

```bash
composer validate --strict
find src tests -name "*.php" -exec php -l {} \;
```

## En cas d'echec

1. Reperez l'etape rouge dans l'onglet `Actions`.
2. Reproduisez localement la commande correspondante.
3. Corrigez le probleme dans le code ou la configuration.
4. Relancez les tests localement.
5. Poussez un nouveau commit ou utilisez `Re-run jobs` dans GitHub.

## Resultat attendu

Quand tout se passe bien, vous devez avoir :

- un workflow `Tests` en vert ;
- un workflow `Quick Checks` en vert ;
- une execution PHPUnit sans erreur.
