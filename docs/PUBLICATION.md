# Liste de contrôle — publication du dépôt

## Publier le code (alpha)

- [x] Séparer le moteur des projets originaux : pas de copie de leurs réponses.
- [x] Licence AGPL-3.0-or-later complète.
- [x] Ajouter les exemples fictifs, les tests et les instructions de lancement.
- [x] Vérification de base des entrées, de la sortie HTML et du stockage privé.
- [x] Documentation des limites et de la procédure de signalement.
- [ ] Faire relire la PR finale et contrôler les résultats CI sur le **dernier SHA**.
- [ ] Fusionner la PR dans `main`.
- [ ] Faire une dernière recherche de secrets/données personnelles dans les commits et fichiers publics.
- [ ] Passer le dépôt en Public depuis GitHub > Settings > General > Danger Zone (action propriétaire).
- [ ] Créer éventuellement une release préliminaire `v0.1.0-alpha` et ajouter une capture réelle de l'interface.

**Texte utilisable pour l'annonce :** « Dendrila Forms est un moteur libre (AGPL) de questionnaires et d'entretiens, en alpha. Deux parcours configurables sont disponibles pour les tests et les contributions. Ce n'est pas encore un service prêt à collecter des données réelles ou sensibles. »

## Héberger un questionnaire de production (non validé en alpha)

- Définir les finalités, responsabilités, informations RGPD, périodes de conservation et droit à l'effacement.
- DocumentRoot obligatoire sur `public/`, vérifier depuis l'extérieur que `src/`, `forms/`, `var/` sont inaccessibles.
- Configurer TLS, HSTS selon le domaine, sessions sécurisées derrière le proxy, sauvegardes privées et cron de purge.
- Faire tester les parcours clavier/lecteurs d'écran et mobile sur les navigateurs cibles.
- Mettre une protection anti-abus au niveau réseau et des limites adaptées au trafic.
- Pour des interviews privées : implémenter authentification d'invitation, relecture et autorisations administrateur.
- Pour des statistiques publiques : concevoir une anonymisation adaptée et prévenir la réidentification.
- N'ouvrir une collecte réelle qu'après audit et test de bout en bout en environnement cible.
