# Todo&Co

Application Symfony pour la gestion de tâches avec authentification et gestion des rôles.

---

## Fonctionnalités

- Création, édition, suppression de tâches
- Marquage des tâches comme terminées
- Liste des tâches par utilisateur
- Authentification (login/logout)
- Gestion des utilisateurs (admin)
- Contrôle d’accès par rôles (`ROLE_USER`, `ROLE_ADMIN`)
- Tests unitaires et fonctionnels
- Fixtures pour peupler la base en dev/test

---

## Installation

### Prérequis

- PHP >= 8.1
- Composer
- MySQL/MariaDB
- DDEV (recommandé pour environnement dev)
- Extensions PHP : pdo_mysql, mbstring, intl

### Étapes

1. **Cloner le projet**
   ```bash
   git clone [<url_du_repo>](https://github.com/Studio509-tom/Todo-Co)
   cd Todo-Co
   ```

2. **Installer les dépendances**
   ```bash
   ddev start
   ddev exec composer install
   ```

3. **Initialiser la base de données**
   ```bash
   ddev exec php bin/console doctrine:database:create --if-not-exists
   ddev exec php bin/console doctrine:migrations:migrate -n
   ddev exec php bin/console doctrine:fixtures:load -n
   ```

---

## Utilisation

- Accès à l’application : [http://localhost:8000](http://localhost:8000) (ou URL DDEV)
- Utilisateurs de test (fixtures) :
  - admin@example.com / adminpass (ROLE_ADMIN)
  - user@example.com / userpass (ROLE_USER)

---

## Structure du projet

| Dossier/Fichier                | Rôle                                      |
| ------------------------------ | ----------------------------------------- |
| src/Entity/                    | Entités Doctrine (User, Task)             |
| src/Controller/                | Contrôleurs Symfony                       |
| src/Form/                      | Formulaires Symfony                       |
| src/DataFixtures/              | Fixtures de test/dev                      |
| src/Repository/                | Requêtes personnalisées                   |
| templates/                     | Vues Twig                                 |
| tests/                         | Tests unitaires/fonctionnels              |
| migrations/                    | Migrations Doctrine                       |

---

## Qualité & Tests

- Lancer les tests :
  ```bash
  ddev exec php bin/phpunit
  ```
- Vérifier la couverture :
  ```bash
  ddev exec XDEBUG_MODE=coverage php bin/phpunit --coverage-html var/coverage
  ```
- Analyse statique :
  ```bash
  ddev exec vendor/bin/phpstan analyse src --level=max
  ```

---

## Contribution

- Respecter le guide de contribution (`Doc_technique_contrib.md`)
- Branches dédiées pour chaque fonctionnalité ou correction
- Commits clairs et formatés
- Pas de migration vide dans une PR
- Tests et qualité obligatoires avant merge

---

## Auteur

Tom & contributeurs Todo&Co

---

Pour toute question, consulte la documentation technique.
