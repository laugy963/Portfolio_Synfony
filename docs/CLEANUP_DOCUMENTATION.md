# 🧹 Documentation - Nettoyage Automatique des Utilisateurs

## Vue d'ensemble

Ce système de nettoyage automatique permet de maintenir la base de données propre en supprimant :
- Les comptes utilisateur non vérifiés après un délai configurable
- Les codes de vérification expirés
- Les données obsolètes

## Commandes disponibles

### 1. Statistiques des utilisateurs
```bash
php bin/console app:user-stats
```
Affiche les statistiques complètes des utilisateurs.

### 2. Nettoyage des comptes non vérifiés
```bash
# Simulation (recommandé en premier)
php bin/console app:cleanup-unverified-users --dry-run

# Suppression effective (par défaut après 7 jours)
php bin/console app:cleanup-unverified-users

# Suppression après 3 jours
php bin/console app:cleanup-unverified-users --days=3
```

### 3. Nettoyage des codes expirés
```bash
php bin/console app:cleanup-expired-codes
```

### 4. Nettoyage planifié automatique
```bash
# Nettoyage quotidien
php bin/console app:scheduled-cleanup

# Nettoyage hebdomadaire (plus agressif)
php bin/console app:scheduled-cleanup --type=weekly

# Avec rapport détaillé
php bin/console app:scheduled-cleanup --report
```

## Configuration Cron (Automatisation)

### 1. Nettoyage quotidien (codes expirés + comptes après 7 jours)
```bash
# Ajouter dans crontab avec : crontab -e
# Exécution tous les jours à 2h00 du matin
0 2 * * * cd /chemin/vers/votre/projet && php bin/console app:scheduled-cleanup >> var/log/cleanup.log 2>&1
```

### 2. Nettoyage hebdomadaire (plus agressif)
```bash
# Exécution tous les dimanches à 3h00 du matin
0 3 * * 0 cd /chemin/vers/votre/projet && php bin/console app:scheduled-cleanup --type=weekly --report >> var/log/cleanup_weekly.log 2>&1
```

### 3. Statistiques quotidiennes
```bash
# Exécution tous les jours à 23h00 pour monitoring
0 23 * * * cd /chemin/vers/votre/projet && php bin/console app:user-stats >> var/log/user_stats.log 2>&1
```

## Stratégies de nettoyage recommandées

### Pour un site en production
- **Quotidien** : Nettoyer les codes expirés + comptes non vérifiés après 7 jours
- **Hebdomadaire** : Nettoyage plus agressif (3 jours) + rapport détaillé

### Pour un site de développement
- **Manuel** : Utiliser `--dry-run` avant toute suppression
- **Flexible** : Ajuster les délais selon les besoins

### Pour un site à fort trafic
- **Quotidien** : Codes expirés seulement
- **Hebdomadaire** : Suppression des comptes non vérifiés
- **Mensuel** : Nettoyage complet avec rapport

## Sécurité et bonnes pratiques

### 1. Sauvegardes
```bash
# Toujours sauvegarder avant un nettoyage important
php bin/console doctrine:schema:dump --env=prod > backup_schema_$(date +%Y%m%d).sql
```

### 2. Tests en mode simulation
```bash
# Toujours tester d'abord en mode --dry-run
php bin/console app:cleanup-unverified-users --dry-run
```

### 3. Monitoring des logs
```bash
# Surveiller les logs de nettoyage
tail -f var/log/cleanup.log
tail -f var/log/cleanup_weekly.log
```

### 4. Alertes email (optionnel)
```bash
# Exemple de script avec notification email
#!/bin/bash
cd /chemin/vers/votre/projet
RESULT=$(php bin/console app:scheduled-cleanup --report 2>&1)
echo "$RESULT" | mail -s "Rapport de nettoyage automatique" admin@votre-site.com
```

## Paramètres configurables

### Dans UserCleanupService.php
- `$daysOld` : Nombre de jours avant suppression (défaut: 7)
- `$dryRun` : Mode simulation (défaut: false)

### Dans ScheduledCleanupService.php
- Nettoyage quotidien : 7 jours
- Nettoyage hebdomadaire : 3 jours

## Logs et monitoring

Les nettoyages automatiques génèrent des logs détaillés :
- Actions effectuées
- Nombre d'éléments traités
- Erreurs rencontrées
- Statistiques avant/après

## Dépannage

### Problème : Commande non trouvée
```bash
# Vérifier que les services sont bien injectés
php bin/console debug:container UserCleanupService
```

### Problème : Erreur de base de données
```bash
# Vérifier la connexion
php bin/console doctrine:schema:validate
```

### Problème : Logs non générés
```bash
# Vérifier les permissions
chmod 755 var/log/
touch var/log/cleanup.log
chmod 644 var/log/cleanup.log
```

## Exemple d'utilisation complète

```bash
# 1. Vérifier l'état actuel
php bin/console app:user-stats

# 2. Simuler le nettoyage
php bin/console app:cleanup-unverified-users --dry-run

# 3. Nettoyer les codes expirés
php bin/console app:cleanup-expired-codes

# 4. Effectuer le nettoyage complet
php bin/console app:cleanup-unverified-users

# 5. Vérifier le résultat
php bin/console app:user-stats
```

## Support

En cas de problème :
1. Vérifier les logs dans `var/log/`
2. Utiliser `--dry-run` pour les tests
3. Consulter les statistiques avec `app:user-stats`
4. Faire une sauvegarde avant les suppressions importantes
