# Sécurité — Dendrila Forms

## Statut et périmètre

La version 0.1.0-alpha est publiée comme **code à évaluer**, pas comme plateforme de collecte en production. N'utilisez ni données de santé, ni données de mineurs, ni témoignages privés, ni informations personnelles de tiers dans les modèles de démonstration.

## Signaler une vulnérabilité

Privilégiez les **GitHub Security Advisories** (onglet Security > Advisories > Report a vulnerability, lorsque disponible), plutôt qu'une issue publique contenant une faille exploitable. Ne publiez jamais de jetons, adresses, données de réponse ou captures privées dans une issue.

## Garanties actuellement implémentées

- Modèles statiques JSON et réponses stockées hors DocumentRoot.
- Code de reprise aléatoire à 256 bits, seule son empreinte est persistée.
- Formulaires POST avec protection CSRF et sessions serveur.
- Cookies HttpOnly et SameSite ; Secure activé uniquement si la connexion PHP est déclarée HTTPS.
- En-têtes de protection CSP, X-Frame-Options, Cache-Control et Referrer-Policy.
- Limites modestes de créations/reprises par IP observée par PHP (sans croire aux en-têtes de proxy non vérifiés).
- Validation serveur des réponses, séparation des formulaires, verrouillage à la soumission, effacement sur demande.
- Purge CLI configurable des dossiers inactifs (non automatique : cron à installer).

## Risques résiduels

- Pas de comptes administrateur, d'OTP ni de contrôle d'accès à des entretiens nominatifs ; **un code de reprise est une clé d'accès complète**.
- Pas d'audit de sécurité indépendant, de sauvegardes chiffrées, ni de procédure de restauration validée.
- Un déploiement derrière un reverse proxy nécessite des réglages TLS/cookies et un filtrage des abus au niveau réseau.
- La conservation et les bases légales doivent être configurées par chaque responsable du traitement.
- Le HTML a des tests automatisés mais aucune certification d'accessibilité n'est revendiquée.

Le code source public n'implique pas la publication de réponses : ces dernières ne doivent jamais être ajoutées au dépôt Git.
