# Dendrila Forms 🌱

Moteur libre et léger de questionnaires et d'entretiens, adapté à un hébergement PHP sans Node.js ni service tiers.

> **Version 0.1.0-alpha — démonstration technique.** Ne pas collecter de données réelles ou sensibles avec ce prototype : les invitations privées, la gestion des personnes, les statistiques, la conservation/purge et l'administration restent à réaliser.

## Tester localement

PHP 8.1+ avec JSON. Depuis la racine du dépôt :

    php -S 127.0.0.1:8080 -t public
    php tests/smoke.php

Ouvrir ensuite http://127.0.0.1:8080. Deux modèles fictifs sont fournis : Enquête et Interview.

## Adapter un questionnaire

Dupliquer un fichier sous `forms/` (ex. `forms/mon-enquete.json`) puis modifier le titre, la description, les sections et les questions. Ouvrir `/?form=mon-enquete`. Types disponibles : `text`, `textarea`, `single`, `multiple`, `scale`. Une question peut définir `show_if: {"question":"id_precedent","equals":"valeur"}` en référence à un choix simple précédent.

Le moteur prévoit une question à la fois, progression, retour arrière, sauvegarde privée, reprise par code aléatoire, relecture, verrouillage à l'envoi et suppression personnelle. Le mode entretien propose l'aperçu de la question suivante. Pas de publication automatique.

## Hébergement

**Pointer impérativement le DocumentRoot sur `public/`**, jamais sur la racine du dépôt. `src/`, `forms/` et `var/` ne doivent pas être publiés. Le stockage par défaut est dans `var/` à côté de `public/`, exclu de Git. Un répertoire privé externe peut être configuré avec `DENDRILA_DATA_DIR=/chemin/absolu/hors-du-web`.

Servir le site en HTTPS. Le code de reprise donne accès aux réponses : conservez-le en lieu sûr. Son secret brut n'est pas conservé sur le serveur (seule son empreinte SHA-256 l'est). Le moteur limite les tentatives de démarrage (8/10 min) et de reprise (24/10 min) par adresse IP vue par PHP, sans stocker l'adresse IP en clair. **Ces limites applicatives ne remplacent pas une protection réseau contre les abus**. Une purge des dossiers inactifs est fournie, mais elle ne s'exécute que si elle est programmée.

Inspiré des UX des questionnaires TDAH et entretiens advarP, sans copier leurs réponses ou données privées. Architecture et limites : [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

## Maintenance et conservation

Les réponses sont conservées en fichiers privés, accessibles avec le code de reprise. Configurer une tâche cron pour la purge des dossiers inactifs (30 jours par défaut) :

    php scripts/purge.php

La variable `DENDRILA_RETENTION_DAYS` accepte une durée de 1 à 365 jours. Les fichiers de limitation de débit sont purgés après 48 h lorsque ce script tourne. Définir les informations de confidentialité, la finalité et les sauvegardes avant toute collecte de réponses réelles. Pour un déploiement public, limiter également les requêtes au niveau du serveur ou proxy.

## Contributions et sécurité

Consulter [CONTRIBUTING.md](CONTRIBUTING.md), [SECURITY.md](SECURITY.md) et [docs/PUBLICATION.md](docs/PUBLICATION.md). Les tests comprennent le lint PHP, les tests métier et un parcours HTTP complet.

**Périmètre alpha :** vous pouvez examiner, tester localement et adapter le moteur avec des données fictives. Il n'est pas prêt pour des entretiens privés réels, les mineurs ou des enquêtes recueillant des données sensibles.

## Licence

AGPL-3.0-or-later — voir [LICENSE](LICENSE). Le dépôt peut rester privé pendant les audits préalables à son ouverture.
