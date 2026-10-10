# Dendrila Forms 🌱

Moteur libre et léger de questionnaires et d'entretiens, adapté à un hébergement PHP sans Node.js ni service tiers.

> **Version 0.1 — démonstration technique.** Ne pas collecter de données réelles ou sensibles avec ce prototype : les invitations privées, la gestion des personnes, les statistiques, la conservation/purge et l'administration restent à réaliser.

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

Servir le site en HTTPS. Le code de reprise donne accès aux réponses : conservez-le en lieu sûr. Son secret brut n'est pas conservé sur le serveur (seule son empreinte SHA-256 l'est). Le prototype ne comporte pas encore de protections anti-abus ni de politique de purge automatique.

Inspiré des UX des questionnaires TDAH et entretiens advarP, sans copier leurs réponses ou données privées. Architecture et limites : [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

## Licence

AGPL-3.0-or-later — voir [LICENSE](LICENSE). Le dépôt peut rester privé pendant les audits préalables à son ouverture.
