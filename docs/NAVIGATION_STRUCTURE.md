# Navigation et Footer Unifiés - Documentation

## Vue d'ensemble

Ce projet utilise maintenant une structure de navigation et de footer unifiée pour toutes les pages. Cette approche garantit une expérience utilisateur cohérente et simplifie la maintenance du code.

## Structure des templates

### Template de base (`base.html.twig`)
Le template principal qui inclut :
- Les dépendances CSS et JavaScript communes (Bootstrap, FontAwesome, etc.)
- La navigation commune (`_navbar.html.twig`)
- Le footer commun (`_footer.html.twig`)
- Les scripts communs de navigation

### Navigation (`_navbar.html.twig`)
- Navigation responsive avec Bootstrap
- Liens dynamiques selon le statut de l'utilisateur (connecté/non connecté)
- Gestion des rôles (admin/utilisateur)
- Indication de la page active

### Footer (`_footer.html.twig`)
- Informations de contact et réseaux sociaux
- Liens de navigation rapide
- Liens légaux (CGU, Politique de confidentialité)
- Bouton de retour en haut de page

## Utilisation dans vos templates

### Template standard
```twig
{% extends 'base.html.twig' %}

{% block title %}Titre de votre page{% endblock %}

{% block body %}
<div class="container page-container content-fade-in">
    <!-- Votre contenu ici -->
</div>
{% endblock %}
```

### Page avec hero banner (comme l'accueil)
```twig
{% extends 'base.html.twig' %}

{% block title %}Titre de votre page{% endblock %}

{% block main_class %}{% endblock %} {# Supprime la classe main-content #}

{% block body %}
    <!-- Votre hero banner et contenu ici -->
{% endblock %}
```

## Classes CSS disponibles

### Structure de page
- `.page-container` : Marge top pour compenser la navbar fixe
- `.main-content` : Conteneur principal avec min-height
- `.content-fade-in` : Animation d'apparition au scroll

### Navigation
- `.current-section` : Appliquée automatiquement au lien actif lors du scroll
- `.nav-link.active` : Indication de la page courante

## Scripts JavaScript

### Script commun (`common-navigation.js`)
Fonctionnalités incluses :
- Gestion responsive de la navigation
- Smooth scroll pour les ancres
- Highlight automatique des sections au scroll
- Auto-fermeture des messages flash
- Gestion du bouton retour en haut
- Tooltips Bootstrap

### Événements disponibles
Le script expose des fonctionnalités que vous pouvez utiliser :
- Animation d'apparition pour les éléments avec la classe `.animate-on-scroll`
- Gestion automatique des tooltips `[data-bs-toggle="tooltip"]`

## Personnalisation

### Modifier la navigation
Éditez `/templates/_navbar.html.twig` pour :
- Ajouter/supprimer des liens
- Modifier les icônes
- Changer la logique d'affichage

### Modifier le footer
Éditez `/templates/_footer.html.twig` pour :
- Modifier les informations de contact
- Ajouter des sections
- Changer les liens sociaux

### CSS personnalisé
Les styles sont dans `/assets/styles/app.css` :
- Variables CSS pour les couleurs
- Styles de navigation responsive
- Animations et transitions

## Avantages de cette approche

1. **Cohérence** : Même navigation sur toutes les pages
2. **Maintenabilité** : Un seul endroit pour modifier la navigation
3. **Performance** : CSS et JS communs mis en cache
4. **Accessibilité** : Navigation clavier, focus management
5. **Responsive** : Adaptation automatique mobile/desktop
6. **SEO** : Structure HTML sémantique

## Migration des pages existantes

Pour adapter une page existante :

1. Supprimez les blocks `stylesheets` et `javascripts` redondants
2. Supprimez la navigation dupliquée dans le template
3. Utilisez la classe `.page-container` pour le contenu
4. Ajoutez `.content-fade-in` pour les animations

Exemple avant :
```twig
{% block stylesheets %}
    {{ parent() }}
    <link href="bootstrap.css" rel="stylesheet">
{% endblock %}

{% block body %}
<nav class="navbar">...</nav>
<div class="container" style="margin-top: 100px;">
```

Exemple après :
```twig
{% block body %}
<div class="container page-container content-fade-in">
```

## Dépannage

### La navigation ne s'affiche pas
- Vérifiez que `_navbar.html.twig` existe
- Vérifiez l'include dans `base.html.twig`

### Les styles ne s'appliquent pas
- Vérifiez que les assets sont compilés
- Vérifiez l'inclusion de `app.css`

### JavaScript ne fonctionne pas
- Vérifiez que Bootstrap JS est chargé
- Vérifiez le fichier `common-navigation.js`
- Regardez la console du navigateur pour les erreurs

Cette structure garantit une expérience utilisateur professionnelle et facilite l'ajout de nouvelles pages au site.
