# Guide de contribution

Merci de vouloir contribuer. Ce document décrit le processus de participation, les exigences de qualité et les règles à respecter.

## 1. Pré-requis

- PHP version alignée avec le projet (vérifier `composer.json`)
- Composer installé
- DDEV (ou stack Docker équivalente) si utilisé
- Extensions : xdebug (optionnel), pdo_mysql
- Outils dev : PHPUnit, PHPStan, PHP CS Fixer (ou équivalent), Symfony CLI (optionnel)

Installation rapide :
```bash
composer install
ddev start          # si DDEV est utilisé
ddev exec php bin/console doctrine:database:create --if-not-exists
ddev exec php bin/console doctrine:migrations:migrate -n
```

## 2. Branches & flux Git

- main : stable, déployable
- develop (si créée) : intégration
- feature/XYZ : nouvelle fonctionnalité
- fix/bug-123 : correction
- chore/tooling, docs/update, test/coverage pour tâches spécifiques

Règles :
- 1 feature = 1 branche = 1 Pull Request (PR)
- Rebase avant ouverture de PR si divergence > 5 commits
- Pas de merge direct sur main

## 3. Commits

Format recommandé (type: portée optionnelle - message)
```
feat(task): ajout filtre tâches terminées
fix(user): correction hash mot de passe null
refactor(security): simplification guard
test(task): ajout test suppression interdit
docs: mise à jour guide contribution
chore: mise à jour dépendances
```
Impératif, en français ou anglais cohérent dans tout le repo.

## 4. Code style

- PSR-12
- Typage PHP 8 (retours + paramètres + propriétés)
- Pas de logique dans les templates Twig
- Préférer Repository pour la logique d’accès aux données (ex: findDoneTasks, reassignTasks)
- Pas de SQL brut sauf nécessité claire + commentaire

Outils (exemple) :
```bash
vendor/bin/php-cs-fixer fix --dry-run
```

## 5. Qualité & outils

Seuils minimum :
- Tests OK (0 échecs)
- Coverage global ≥ 70% (viser > 80% pour nouvelles features)
- PHPStan level max (pas d’erreurs bloquantes)
- Pas de TODO non justifiés

Commandes :
```bash
vendor/bin/phpunit
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text
vendor/bin/phpstan analyse src --level=max
```

## 6. Tests

- Chaque bug fixé => test qui reproduit l’ancien échec
- Nommer clairement : testUserCannotDeleteTaskOfAnotherUser
- Types : unit (logique pure), functional (HTTP layer), repository (requêtes)
- Fixtures isolées (pas de dépendance entre tests)
- Nettoyage : supprimer entités créées ou utiliser transactions rollback

## 7. Entités & Migrations

- Toute modification d’entité => `php bin/console make:migration`
- Interdire les changements destructifs sans script de migration des données
- Pas de migration vide dans une PR

## 8. Sécurité

- Ne jamais logguer mot de passe ou hash
- Vérifier contrôles d’accès (`isGranted`, ownership) dans contrôleurs ou voters
- Pas d’exceptions silencieuses
- Vérifier CSRF sur actions POST/DELETE

## 9. Revue de code (Pull Request)

Checklist auteur :
- [ ] Description claire (quoi / pourquoi)
- [ ] Tests ajoutés / adaptés
- [ ] Pas d’erreurs PHPStan
- [ ] Style conforme
- [ ] Migrations incluses si besoin
- [ ] Pas de secrets commités

Checklist reviewer :
- Lisibilité
- Performance raisonnable (pas de N+1 évident)
- Respect séparation des responsabilités
- Absence de duplication évidente
- Cohérence messages utilisateur

## 10. Nommage & conventions

| Élément        | Convention                            |
| -------------- | -------------------------------------- |
| Entités        | PascalCase (Task, User)               |
| Propriétés     | camelCase                             |
| Méthodes       | camelCase (findDoneTasks)             |
| Templates      | snake_case (list_done.html.twig)      |
| Routes         | kebab-case (ex: task-delete)          |
| Variables tests| explicites (`$nonAuthorUser`)         |

## 11. Gestion des tâches & rôles

Règles métier principales (résumé) :
- Suppression tâche : auteur OU admin

## 12. Performance

- Préférer QueryBuilder/Repository à load + filtrage PHP
- Ajouter index DB si requête fréquente (ex: is_done, author_id)
- Éviter `clear()` abusif sauf après DQL mass update

## 13. Documentation

- Ajouter exemples d’utilisation si endpoint ou service nouveau

## 14. Dépendances

- Vérifier changelog avant mise à jour majeure
- Pas de dépendance non utilisée
- Bloquer version majeure dans composer.json sauf nécessité

## 15. Erreurs fréquentes (prévention)

| Problème | Prévention |
| -------- | ---------- |
| Duplicate user | Nettoyage tests / fixtures isolées |
| Array to string roles | Setter normalisant valeur en array |
| Tâches orphelines | ReassignTasks avant delete user |
| 302 au lieu de 403 | Vérifier loginUser dans tests |

## 16. Processus PR (résumé)

1. Créer branche
2. Développer + tests
3. Lancer outils qualité
4. Mettre à jour migrations/doc

---

Merci de respecter ces règles pour garder le projet stable, lisible et maintenable.