#!/bin/bash

# Script pour tester l'envoi d'emails de réinitialisation de mot de passe
# Ce script teste que les emails sont bien envoyés et que les liens fonctionnent

echo "=== Test d'envoi d'email de réinitialisation de mot de passe ==="
echo ""

# Exécuter les tests de reset password avec des détails
echo "Exécution des tests d'email de réinitialisation..."
echo ""

php bin/phpunit tests/Controller/ResetPasswordEmailTest.php --testdox --verbose

echo ""
echo "=== Fin des tests ==="
