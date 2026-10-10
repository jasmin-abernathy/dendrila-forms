# Architecture v0.1

- `forms/*.json` : modèles configurables, modes `survey` et `interview`, sections/questions ordonnées.
- `src/Core.php` : validation de schéma, visibilité, typage, réponses obligatoires.
- `src/Storage.php` : fichiers privés, contrôle de répertoire, code de reprise aléatoire et son empreinte.
- `src/Flow.php` + `src/Navigation.php` : navigation et verrouillage à l'envoi.
- `public/` : seul DocumentRoot autorisé, rendu HTML serveur accessible sans JavaScript.
- `tests/` : régressions du moteur.

Le moteur reprend les principes de l'enquête TDAH V2 (progression, conditionnels, reprise) et des entretiens advarP (séquences éditoriales, longues réponses, aperçu suivant), sans transférer de données. Les invitations OTP, l'authentification des personnes, les révisions rédactionnelles, les pièces jointes et les statistiques anonymisées à seuil sont hors v0.1.

Avant production : protéger contre les abus, prévoir une limite de conservation et une purge, établir les bases légales/informations RGPD, ajouter un modèle administrateur et authentification adaptée, tester Apache/Nginx, TLS et l'accessibilité réelle. Aucune publication des réponses n'est prévue dans le prototype.
