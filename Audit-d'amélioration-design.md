## 1. Méthode d’audit
Audit statique du front réel du dépôt, centré sur le rendu navigateur: layout global [templates/base.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/base.html.twig:1), navigation/footer [templates/_navbar.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/_navbar.html.twig:1) et [templates/_footer.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/_footer.html.twig:1), pages Twig publiques et privées, styles [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:1) et [assets/styles/pages.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/pages.css:1), scripts front [assets/js/common-navigation.js](/Users/king/Documents/FAC/Portfolio_Synfony/assets/js/common-navigation.js:1), [assets/js/account-settings.js](/Users/king/Documents/FAC/Portfolio_Synfony/assets/js/account-settings.js:1), [assets/js/project-gallery.js](/Users/king/Documents/FAC/Portfolio_Synfony/assets/js/project-gallery.js:1), [assets/js/project-reorder.js](/Users/king/Documents/FAC/Portfolio_Synfony/assets/js/project-reorder.js:1), [assets/js/password-toggle.js](/Users/king/Documents/FAC/Portfolio_Synfony/assets/js/password-toggle.js:1), et FormType qui pilotent les classes/labels visibles.

J’ai aussi relié les templates aux FormType [src/Form/RegistrationFormType.php](/Users/king/Documents/FAC/Portfolio_Synfony/src/Form/RegistrationFormType.php:1), [src/Form/ProjectType.php](/Users/king/Documents/FAC/Portfolio_Synfony/src/Form/ProjectType.php:1), [src/Form/ProfilEditType.php](/Users/king/Documents/FAC/Portfolio_Synfony/src/Form/ProfilEditType.php:1), [src/Form/VerificationCodeType.php](/Users/king/Documents/FAC/Portfolio_Synfony/src/Form/VerificationCodeType.php:1), [src/Form/ChangePasswordType.php](/Users/king/Documents/FAC/Portfolio_Synfony/src/Form/ChangePasswordType.php:1) et [src/Form/ChangePasswordFormType.php](/Users/king/Documents/FAC/Portfolio_Synfony/src/Form/ChangePasswordFormType.php:1). Hypothèse explicite: l’audit est fondé sur la cascade CSS/HTML/JS réelle du code, sans passe navigateur dans ce tour.

## 2. Vue d’ensemble
Le site est fonctionnel, structuré et montre une vraie intention visuelle, surtout sur la home et la fiche projet publique. La base SEO est correcte, les parcours existent, et certains composants ont déjà une direction claire.

Le problème principal est systémique: le projet ressemble visuellement à plusieurs mini-sites assemblés. La home marketing, les formulaires d’authentification, les écrans profil/admin et les pages légales n’utilisent pas le même design system. La lisibilité souffre surtout sur le hero, sur les textes secondaires en contexte sombre, sur les retours d’erreur peu harmonisés et sur une microcopy française inégale. Impression générale: sérieux techniquement, mais encore trop fragmenté visuellement pour paraître pleinement fini.

## 3. Problèmes détaillés
1. **Cohérence visuelle : CSS contradictoire et dette de styles**
Où : [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:52), [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:353), [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:728), [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:846), [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:1269), [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:1655), [assets/styles/pages.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/pages.css:1).
Pourquoi : `navbar`, `nav-link`, `card`, `btn`, `alert`, `form-container` et même le carrousel sont redéfinis plusieurs fois; le rendu dépend trop de l’ordre de cascade et devient imprévisible.
Priorité : critique.
Recommandation : reconstruire une source unique de vérité pour les tokens, composants et variantes.
Suggestion de correction : séparer `tokens.css`, `layout.css`, `components.css`, `home.css`, `auth.css`, `admin.css`; supprimer les sélecteurs morts comme `.carousel-dots`/`.carousel-progress` qui ne correspondent pas au markup actuel.
Impact attendu : cohérence globale nette et maintenance front beaucoup plus simple.

2. **Lisibilité / hiérarchie : hero home peu lisible et trop agité**
Où : [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:283), [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:316), [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:941), [templates/home/index.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/home/index.html.twig:12).
Pourquoi : l’overlay est au-dessus du contenu (`z-index:10` vs `2`), le `h1` et le paragraphe sont forcés en bleu sur des photos, le CTA secondaire est bleu sur image, et le carrousel auto-rotatif détourne l’attention du message principal.
Priorité : critique.
Recommandation : stabiliser le hero autour d’un seul message lisible immédiatement.
Suggestion de correction : mettre le contenu au-dessus de l’overlay, forcer texte/CTA en blanc ou quasi-blanc, augmenter l’opacité de l’overlay, et désactiver l’autoplay ou ajouter un contrôle de pause.
Impact attendu : meilleure compréhension de la proposition de valeur dès la première seconde.

3. **Cohérence de layout : rythme vertical bricolé au cas par cas**
Où : [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:187), [templates/legal/privacy_policy.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/legal/privacy_policy.html.twig:9), [templates/legal/terms_of_service.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/legal/terms_of_service.html.twig:9), [templates/legal/mentions_legales.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/legal/mentions_legales.html.twig:8), [templates/reset_password/request.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/reset_password/request.html.twig:15), [templates/reset_password/check_email.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/reset_password/check_email.html.twig:10).
Pourquoi : la navbar fixe est compensée par `page-container`, puis par des `margin-top` inline et des `offset-md-2`; les débuts de page ne sont pas réguliers.
Priorité : importante.
Recommandation : normaliser un shell de page unique.
Suggestion de correction : créer un composant `page-header` avec spacing fixe, supprimer tous les `style="margin-top:2.5rem"` et centrer les écrans reset avec `col-md-6 mx-auto`.
Impact attendu : alignement plus propre entre pages desktop/mobile.

4. **Qualité des formulaires : trois langages UI différents**
Où : [templates/security/login.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/security/login.html.twig:31), [templates/registration/register.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/registration/register.html.twig:24), [templates/reset_password/request.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/reset_password/request.html.twig:23), [templates/registration/verify_code.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/registration/verify_code.html.twig:18), [templates/profil/edit.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/profil/edit.html.twig:41), [templates/project/_form.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/project/_form.html.twig:3), [src/Form/RegistrationFormType.php](/Users/king/Documents/FAC/Portfolio_Synfony/src/Form/RegistrationFormType.php:23), [src/Form/ProfilEditType.php](/Users/king/Documents/FAC/Portfolio_Synfony/src/Form/ProfilEditType.php:21), [src/Form/ProjectType.php](/Users/king/Documents/FAC/Portfolio_Synfony/src/Form/ProjectType.php:33).
Pourquoi : auth = champs custom `form-input`, vérification/profil/admin = `form-control` Bootstrap; mêmes usages, apparences différentes.
Priorité : importante.
Recommandation : définir un seul système de formulaires pour tout le site.
Suggestion de correction : standardiser labels, inputs, aides, erreurs, boutons primaires/secondaires et appliquer ce kit à tous les FormType/Twig.
Impact attendu : meilleure cohérence de marque et réduction du coût cognitif sur les parcours compte/admin.

5. **Clarté des feedbacks : erreurs brutes et langues mélangées**
Où : [config/packages/twig.yaml](/Users/king/Documents/FAC/Portfolio_Synfony/config/packages/twig.yaml:1), [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:1739), [templates/registration/register.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/registration/register.html.twig:35), [templates/reset_password/reset.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/reset_password/reset.html.twig:47), [src/Form/ResetPasswordRequestFormType.php](/Users/king/Documents/FAC/Portfolio_Synfony/src/Form/ResetPasswordRequestFormType.php:16), [src/Form/ChangePasswordFormType.php](/Users/king/Documents/FAC/Portfolio_Synfony/src/Form/ChangePasswordFormType.php:27).
Pourquoi : aucun thème de formulaire n’est configuré, `.form-error` n’est pas branché, et plusieurs messages/labels visibles peuvent sortir en anglais.
Priorité : importante.
Recommandation : harmoniser le feedback de validation et localiser 100% des microcopies.
Suggestion de correction : activer un thème de formulaire cohérent ou créer des blocks Twig custom; déplacer les textes visibles dans `translations/`.
Impact attendu : erreurs mieux comprises, ressenti plus professionnel et plus rassurant.

6. **Responsive / composants : style global des boutons trop invasif**
Où : [assets/styles/app.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/app.css:901), [templates/project/index.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/project/index.html.twig:41), [templates/project/index.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/project/index.html.twig:79), [templates/project/index.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/project/index.html.twig:138), [templates/profil/index.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/profil/index.html.twig:82), [templates/profil/edit.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/profil/edit.html.twig:65).
Pourquoi : tous les `.btn`, y compris les `.btn-sm` d’admin, prennent un padding large et une forme pilule; les tables et zones d’actions deviennent visuellement lourdes et plus fragiles sur petit écran.
Priorité : importante.
Recommandation : limiter le style “CTA marketing” à quelques boutons clés.
Suggestion de correction : créer une classe dédiée type `.btn-cta`; laisser Bootstrap gérer `btn-sm`, `btn-icon`, `btn-table`.
Impact attendu : meilleure densité, actions plus lisibles, moins de risques de débordement mobile.

7. **Page projet publique : colonne vide possible et accessibilité légère**
Où : [templates/project/public_show.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/project/public_show.html.twig:68), [assets/styles/pages.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/pages.css:240), [assets/styles/pages.css](/Users/king/Documents/FAC/Portfolio_Synfony/assets/styles/pages.css:275), [assets/js/project-gallery.js](/Users/king/Documents/FAC/Portfolio_Synfony/assets/js/project-gallery.js:1).
Pourquoi : l’`aside` est rendu même sans galerie, ce qui peut laisser un panneau vide; les contrôles de galerie n’ont pas de focus visible dédié ni d’indicateur d’état.
Priorité : importante.
Recommandation : conditionner la colonne secondaire et enrichir l’affordance du carrousel.
Suggestion de correction : rendre l’`aside` seulement si `project.images` existe; ajouter `:focus-visible`, compteur “1/4” ou miniatures, et états hover/focus plus nets.
Impact attendu : fiche projet plus propre et plus accessible clavier.

8. **Pages légales : incohérentes, dupliquées et partiellement incomplètes**
Où : [templates/legal/privacy_policy.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/legal/privacy_policy.html.twig:118), [templates/legal/terms_of_service.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/legal/terms_of_service.html.twig:91), [templates/legal/mentions_legales.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/legal/mentions_legales.html.twig:36), [templates/legal/mentions_legales.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/legal/mentions_legales.html.twig:20).
Pourquoi : même CSS copié dans trois fichiers, styles inline répétés, et placeholders d’hébergeur visibles sur une page sensible pour la crédibilité.
Priorité : critique.
Recommandation : traiter les pages légales comme un composant éditorial partagé.
Suggestion de correction : créer un template/partial légal commun dans le CSS global et remplacer immédiatement les placeholders par des données réelles.
Impact attendu : meilleur niveau de confiance et conformité plus crédible.

9. **Navigation / perception : orientation faible et branding trop générique**
Où : [templates/_navbar.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/_navbar.html.twig:20), [assets/js/common-navigation.js](/Users/king/Documents/FAC/Portfolio_Synfony/assets/js/common-navigation.js:87), [templates/base.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/base.html.twig:5), [templates/_footer.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/_footer.html.twig:7).
Pourquoi : l’état actif fonctionne surtout pour la home et quelques routes compte; les pages projet/légales/admin ne sont pas clairement situées; favicon `sf` et branding “Portfolio” restent génériques; la copie française perd souvent ses accents.
Priorité : importante.
Recommandation : renforcer wayfinding et identité.
Suggestion de correction : ajouter des états actifs par route, remplacer le favicon temporaire, choisir un nom/logo plus distinctifs et faire une passe éditoriale française complète.
Impact attendu : meilleur repérage, meilleure mémorisation de marque, ressenti plus fini.

10. **Parcours destructifs : affordance inversée et confirmations redondantes**
Où : [templates/profil/index.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/profil/index.html.twig:141), [assets/js/account-settings.js](/Users/king/Documents/FAC/Portfolio_Synfony/assets/js/account-settings.js:8), [templates/project/index.html.twig](/Users/king/Documents/FAC/Portfolio_Synfony/templates/project/index.html.twig:85).
Pourquoi : le bouton supprimer devient `outline` quand l’action est validée et reste rouge plein quand il est désactivé; le compte demande à la fois une saisie “SUPPRIMER” et un `window.confirm`; la suppression projet repose sur une popup navigateur brute.
Priorité : importante.
Recommandation : unifier un seul pattern de confirmation destructive.
Suggestion de correction : inverser les classes du bouton, supprimer la deuxième confirmation et remplacer le `onsubmit` inline par une modale cohérente.
Impact attendu : moins de friction, meilleur signal visuel, UX plus maîtrisée.

## 4. Top 10 priorités
1. Corriger immédiatement le hero de la home: ordre des couches, contraste texte/CTA, autoplay.
2. Dédupliquer la CSS et arrêter les redéfinitions globales concurrentes.
3. Unifier un design system de formulaires pour auth, profil, vérification et admin.
4. Sortir le style “gros bouton pilule” des écrans admin et des `btn-sm`.
5. Supprimer tous les offsets/margins inline de compensation sous la navbar fixe.
6. Centraliser les pages légales dans un layout partagé et remplir les données d’hébergeur.
7. Harmoniser tous les messages visibles et validations en français.
8. Corriger la fiche projet publique pour ne pas afficher de colonne vide et mieux traiter la galerie.
9. Renforcer les états actifs de navigation et remplacer le favicon/branding générique.
10. Refaire les confirmations destructives pour éviter les doubles prompts et les signaux visuels inversés.

## 5. Plan d’amélioration
Lot 1, quick wins : hero home, remplissage des mentions légales, inversion de l’état du bouton de suppression, suppression des `style=""` et `offset-md-2`, correction des textes FR les plus visibles.

Lot 2, corrections structurelles : refonte des boutons en variantes distinctes `cta/admin/icon`, unification des champs et erreurs de formulaires, conditionnement de l’`aside` projet, meilleure couleur de texte secondaire sur fonds sombres.

Lot 3, harmonisation globale : refactor CSS en couches, composants partagés `page-header`, `legal-page`, `content-card`, `feedback`, et normalisation navbar/footer/sections.

Lot 4, accessibilité / responsive / finition : focus visibles sur galerie et liens custom, revue mobile des tables/actions admin, cohérence des messages de validation, audit clavier complet des parcours compte/projet.

Design system implicite manquant :
- une échelle de spacing claire
- une échelle typographique par niveau de titre et texte secondaire
- une palette avec règles d’usage par contexte clair/sombre
- des variantes de boutons documentées
- une spécification unique pour labels, aides, erreurs et champs
- des composants réutilisables pour `page-header`, `legal-content`, `project-card`, `admin-actions`

## 6. Note finale
Design : **5/10**. Il y a de bonnes intentions visuelles, mais pas encore de système stable; la cohérence inter-pages est le point faible majeur.

Lisibilité : **5/10**. Les contenus de base sont lisibles dans les cartes et pages blanches, mais le hero, certains textes secondaires sombres et les feedbacks de formulaire tirent la note vers le bas.

Expérience utilisateur : **5/10**. Les parcours existent et couvrent bien le besoin, mais la navigation, les confirmations destructives, l’uniformité des composants et la perception de finition doivent encore monter d’un cran.