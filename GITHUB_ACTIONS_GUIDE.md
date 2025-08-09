# 🧪 Guide de test GitHub Actions

## Comment vérifier que vos tests automatiques fonctionnent

### 1. 🔗 Accédez à GitHub Actions

1. Allez sur votre repository GitHub : `https://github.com/laugy963/Portfolio_Synfony`
2. Cliquez sur l'onglet **"Actions"**
3. Vous devriez voir les workflows qui s'exécutent automatiquement

### 2. ✅ Tests déclenchés automatiquement

Après votre dernier push, vous devriez voir :

```
🟡 Tests - En cours...
🟡 CI Quick - En cours...
```

Puis après quelques minutes :

```
✅ Tests - Réussi (4 tests, 23 assertions)
✅ CI Quick - Réussi
```

### 3. 🔍 Voir les détails des tests

Cliquez sur un workflow pour voir :
- Les étapes d'installation
- L'exécution de vos tests PHPUnit
- Les résultats détaillés

### 4. 🧪 Test manuel immédiat

Pour déclencher les tests maintenant, faites un petit changement :

```bash
# Modifiez un fichier et committez
echo "# Tests GitHub Actions configurés ✅" >> README.md
git add README.md
git commit -m "Test GitHub Actions"
git push origin feature/motdepasseoublier
```

### 5. 📊 Résultats attendus

Vos tests de réinitialisation de mot de passe devraient s'exécuter :

```
✓ Reset password email is sent
✓ Reset password email not sent for inexistent user  
✓ Reset password link works
✓ Invalid token does not work

OK (4 tests, 23 assertions)
```

### 6. 🚨 En cas de problème

Si les tests échouent :

1. **Cliquez sur le workflow échoué**
2. **Consultez les logs** pour voir l'erreur
3. **Corrigez le problème** dans votre code
4. **Committez et poussez** → Les tests se relancent automatiquement

### 7. 🎯 Prochaines étapes

Une fois que tout fonctionne :

1. **Créez une Pull Request** vers `main`
2. **Les tests s'exécuteront automatiquement** avant le merge
3. **Merge impossible** si les tests échouent (protection)

## 🔧 Configuration avancée

Pour personnaliser les workflows, modifiez :
- `.github/workflows/tests.yml` - Tests complets
- `.github/workflows/quick-check.yml` - Vérifications rapides

---

**🎉 Félicitations ! Vos tests s'exécutent maintenant automatiquement à chaque push !**
