# Contribuer à Dendrila Forms

Les retours UX, traductions, améliorations d'accessibilité, tests et correctifs sont les bienvenus.

1. Créer une issue décrivant le besoin ou le défaut, sans données personnelles.
2. Proposer une branche dédiée, avec une petite PR et des étapes de reproduction.
3. Exécuter `php tests/smoke.php` et `python3 tests/http_smoke.py` (PHP 8.1+ requis).
4. Préserver la compatibilité du rendu sans JavaScript, les parcours enquête/interview et la séparation du stockage.
5. Ajouter un test de régression si un comportement a été corrigé.

Ne fournir aucun témoignage réel, code de reprise, mot de passe, donnée de santé ou adresse e-mail privée dans le dépôt et les tickets.

Avant tout commit, consulter `AGENTS.md` et les règles de `repo-factory` ; éviter les builds CI inutiles. Les grandes propositions de fonctionnalités doivent commencer par une discussion, sans casser la compatibilité des formulaires existants.

Licence des contributions au code : AGPL-3.0-or-later.
