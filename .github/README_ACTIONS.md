# GitHub Actions - Configuration des tests automatiques

## 📋 Vue d'ensemble

Ce projet utilise GitHub Actions pour exécuter automatiquement tous les tests à chaque push vers le repository.

## 🚀 Workflows configurés

### 1. Tests complets (`tests.yml`)

**Déclenché sur :**
- Push vers `main` seulement
- Pull requests vers `main`

**Ce qui est testé :**
- ✅ Installation des dépendances PHP avec Composer
- ✅ Validation du `composer.json`
- ✅ Configuration de PostgreSQL comme base de données de test
- ✅ Exécution des migrations Doctrine
- ✅ Chargement des fixtures (données de test)
- ✅ **Tous vos tests PHPUnit** incluant :
  - Tests de réinitialisation de mot de passe
  - Tests des contrôleurs
  - Tests des entités
  - Tests des services
- ✅ Tests sur plusieurs versions de PHP (8.2, 8.3)

### 2. Vérifications rapides (`quick-check.yml`)

**Déclenché sur :**
- Push vers `main` seulement
- Pull requests vers `main`

**Vérifications légères :**
- ✅ Syntaxe PHP valide
- ✅ Validation Composer
- ✅ Vérification des requirements Symfony

## 🔧 Configuration automatique

Les workflows sont configurés pour :

1. **Utiliser la même base de données** que votre projet (PostgreSQL)
2. **Installer toutes les dépendances** automatiquement
3. **Configurer l'environnement de test** avec les bonnes variables
4. **Exécuter TOUS vos tests** sans exception

## 📊 Résultats des tests

Après chaque push, vous verrez :

```
✅ Tests (ubuntu-latest, 8.2)
✅ Tests (ubuntu-latest, 8.3)  
✅ Vérifications rapides
```

### Exemple de sortie réussie :
```
PHPUnit 12.3.0 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.2.24

Reset password email is sent                                    ✓
Reset password email not sent for inexistent user              ✓
Reset password link works                                       ✓
Invalid token does not work                                     ✓

Time: 00:02.456, Memory: 124.00 MB

OK (4 tests, 23 assertions)
```

## 🐛 Débuggage

Si les tests échouent dans GitHub Actions :

1. **Vérifiez l'onglet "Actions"** dans votre repository GitHub
2. **Cliquez sur le workflow échoué** pour voir les détails
3. **Consultez les logs** de chaque étape

### Erreurs communes et solutions :

**Base de données :**
```bash
# Si erreur de connexion DB
DATABASE_URL=postgresql://app:!ChangeMe!@127.0.0.1:5432/app_test
```

**Dépendances manquantes :**
```bash
# Si erreur Composer
composer install --no-dev --optimize-autoloader
```

**Variables d'environnement :**
```bash
# Si erreur de configuration
cp .env.test .env.test.local
```

## 🔄 Comment ça marche

1. **Push de code** → Déclenche automatiquement les workflows
2. **GitHub Actions** démarre des containers Ubuntu avec PHP et PostgreSQL
3. **Installation** de toutes les dépendances
4. **Configuration** de la base de données de test
5. **Exécution** de tous vos tests PHPUnit
6. **Rapport** des résultats (✅ succès / ❌ échec)

## 🎯 Avantages

- ✅ **Tests automatiques** à chaque push
- ✅ **Détection précoce** des problèmes
- ✅ **Tests sur plusieurs versions** de PHP
- ✅ **Validation avant merge** des pull requests
- ✅ **Historique complet** des tests dans GitHub

## 🚨 Points importants

1. **Tous vos tests existants** sont inclus automatiquement
2. **La base de données est recréée** à chaque exécution
3. **Les emails de test** sont capturés (pas d'envoi réel)
4. **Échec = pas de merge** possible (protection des branches)

## 📝 Modification des workflows

Pour modifier les workflows :

1. Éditez les fichiers dans `.github/workflows/`
2. Commitez les changements
3. Les nouveaux workflows s'appliquent automatiquement

## 🔗 Liens utiles

- [Documentation GitHub Actions](https://docs.github.com/en/actions)
- [Actions pour PHP](https://github.com/marketplace/actions/setup-php-action)
- [Documentation PHPUnit](https://phpunit.de/documentation.html)
