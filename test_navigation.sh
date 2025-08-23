#!/bin/bash

# Script de test de la navigation unifiée
echo "🚀 Test de la navigation et du footer unifiés"
echo "============================================="

cd /Users/king/Documents/Portfolio_Synfony

# Vérifier que les fichiers existent
echo "📁 Vérification des fichiers..."

if [ -f "templates/_navbar.html.twig" ]; then
    echo "✅ Navigation template existe"
else
    echo "❌ Navigation template manquant"
fi

if [ -f "templates/_footer.html.twig" ]; then
    echo "✅ Footer template existe"
else
    echo "❌ Footer template manquant"
fi

if [ -f "public/js/common-navigation.js" ]; then
    echo "✅ Script JavaScript commun existe"
else
    echo "❌ Script JavaScript commun manquant"
fi

# Vérifier les CSS
if grep -q "NAVIGATION COMMUNE" assets/styles/app.css; then
    echo "✅ Styles de navigation ajoutés"
else
    echo "❌ Styles de navigation manquants"
fi

if grep -q "FOOTER COMMUN" assets/styles/app.css; then
    echo "✅ Styles de footer ajoutés"
else
    echo "❌ Styles de footer manquants"
fi

echo ""
echo "🔧 Compilation des assets..."
npm run build 2>/dev/null || yarn build 2>/dev/null || echo "⚠️  Pas de compilation d'assets configurée"

echo ""
echo "🌐 Vérification du serveur..."
if pgrep -f "symfony.*server" > /dev/null; then
    echo "✅ Serveur Symfony actif sur http://127.0.0.1:8000"
    echo ""
    echo "📋 Pages à tester :"
    echo "   • Accueil : http://127.0.0.1:8000/"
    echo "   • Connexion : http://127.0.0.1:8000/login"
    echo "   • Inscription : http://127.0.0.1:8000/register"
    echo "   • Profil : http://127.0.0.1:8000/profil"
    echo "   • Gestion : http://127.0.0.1:8000/admin/projects"
    echo ""
    echo "✨ Points à vérifier :"
    echo "   ✓ Navigation identique sur toutes les pages"
    echo "   ✓ Footer présent partout"
    echo "   ✓ Bouton retour en haut"
    echo "   ✓ Navigation responsive (mobile)"
    echo "   ✓ Liens actifs surlignés"
    echo "   ✓ Smooth scroll sur la page d'accueil"
else
    echo "❌ Serveur Symfony non actif"
    echo "💡 Démarrez-le avec : symfony server:start"
fi

echo ""
echo "📖 Documentation disponible : docs/NAVIGATION_STRUCTURE.md"
