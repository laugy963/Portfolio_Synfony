#!/bin/bash

# 🧹 Script de configuration automatique du nettoyage
# Utilisation : ./setup_cleanup_cron.sh

echo "🧹 Configuration du nettoyage automatique"
echo "=========================================="

# Obtenir le chemin absolu du projet
PROJECT_DIR=$(pwd)
echo "📁 Projet détecté : $PROJECT_DIR"

# Créer le répertoire de logs s'il n'existe pas
mkdir -p var/log
chmod 755 var/log

# Créer les fichiers de logs
touch var/log/cleanup.log
touch var/log/cleanup_weekly.log
touch var/log/user_stats.log
chmod 644 var/log/*.log

echo "📝 Fichiers de logs créés"

# Générer les commandes cron
echo ""
echo "📅 Configuration cron recommandée :"
echo "==================================="
echo ""
echo "# Nettoyage quotidien à 2h00"
echo "0 2 * * * cd $PROJECT_DIR && php bin/console app:scheduled-cleanup >> var/log/cleanup.log 2>&1"
echo ""
echo "# Nettoyage hebdomadaire (dimanche à 3h00)"
echo "0 3 * * 0 cd $PROJECT_DIR && php bin/console app:scheduled-cleanup --type=weekly --report >> var/log/cleanup_weekly.log 2>&1"
echo ""
echo "# Statistiques quotidiennes à 23h00"
echo "0 23 * * * cd $PROJECT_DIR && php bin/console app:user-stats >> var/log/user_stats.log 2>&1"
echo ""

# Proposer d'ajouter automatiquement au crontab
read -p "Voulez-vous ajouter ces tâches au crontab automatiquement ? (y/N) : " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]
then
    # Sauvegarder le crontab actuel
    crontab -l > /tmp/crontab_backup_$(date +%Y%m%d_%H%M%S) 2>/dev/null || echo "# Nouveau crontab" > /tmp/crontab_backup_$(date +%Y%m%d_%H%M%S)
    
    # Ajouter les nouvelles tâches
    (crontab -l 2>/dev/null; echo ""; echo "# Portfolio - Nettoyage automatique"; echo "0 2 * * * cd $PROJECT_DIR && php bin/console app:scheduled-cleanup >> var/log/cleanup.log 2>&1"; echo "0 3 * * 0 cd $PROJECT_DIR && php bin/console app:scheduled-cleanup --type=weekly --report >> var/log/cleanup_weekly.log 2>&1"; echo "0 23 * * * cd $PROJECT_DIR && php bin/console app:user-stats >> var/log/user_stats.log 2>&1") | crontab -
    
    echo "✅ Tâches cron ajoutées avec succès !"
    echo "📋 Pour voir le crontab : crontab -l"
    echo "🗑️  Pour supprimer : crontab -r"
else
    echo "⏸️  Configuration manuelle requise"
    echo "📋 Copiez-collez les commandes ci-dessus dans votre crontab avec : crontab -e"
fi

echo ""
echo "🧪 Tests disponibles :"
echo "====================="
echo ""
echo "# Statistiques actuelles"
echo "php bin/console app:user-stats"
echo ""
echo "# Test de nettoyage (simulation)"
echo "php bin/console app:cleanup-unverified-users --dry-run"
echo ""
echo "# Nettoyage automatique avec rapport"
echo "php bin/console app:scheduled-cleanup --report"
echo ""

echo "📚 Documentation complète disponible dans : docs/CLEANUP_DOCUMENTATION.md"
echo ""
echo "✅ Configuration terminée !"
