# Vehicle Fleet Parking Management

Application d'exercice DDD/CQRS permettant de gérer l'enregistrement et la localisation de véhicules au sein d'une flotte.  
Le projet couvre :
- des règles métier simples (enregistrement unique par flotte, localisation non répétée) ;
- une CLI Symfony Console pour piloter l'application ;
- deux profils de tests Behat (mémoire et persistance PostgreSQL).

## Prérequis

- PHP ≥ 8.1 avec extensions PDO PostgreSQL
- Composer
- PostgreSQL

## Installation

```bash
composer install
```

Configurer la connexion PostgreSQL dans `.env` :

```
DATABASE_URL="postgresql://user:password@localhost:5432/fleet_management?serverVersion=18&charset=utf8"
```

Créer la base et appliquer la migration :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

## Exécution des tests BDD

- **In-memory (profil par défaut)**  
  ```bash
  ./vendor/bin/behat
  ```

- **Persistance PostgreSQL**  
  ```bash
  ./vendor/bin/behat -p persistence
  ```

## Commandes CLI disponibles

```bash
php bin/console fleet:create <userId>
php bin/console fleet:register-vehicle <fleetId> <vehiclePlateNumber>
php bin/console fleet:localize-vehicle <fleetId> <vehiclePlateNumber> <lat> <lng> [alt]
```

## Structure du projet

```
src/
  Kernel.php      # Kernel Symfony (emplacement standard)
  App/            # Commandes, queries et handlers (couche application)
  Domain/         # Modèle métier & Value Objects
  Infra/          # Implémentations de repositories (InMemory + Doctrine), entités ORM
  UserInterface/  # Commandes console Symfony
features/         # Scénarios Behat (register_vehicle, park_vehicle)
features/bootstrap/  # Contextes Behat (mémoire & persistance)
```

## Principes appliqués

- **DDD** : Modèle de domaine riche avec entités (Fleet, Vehicle, User) et Value Objects (FleetId, VehicleId, Location, UserId)
- **CQRS** : Séparation commandes (RegisterVehicle, ParkVehicle) et queries (GetVehicleLocation)
- **Hexagonal Architecture** : Le domaine est isolé, les repositories sont des interfaces
- **Symfony Console** : CLI pour exposer les fonctionnalités
- **Doctrine ORM** : Persistence PostgreSQL avec entités d'infrastructure séparées du domaine
- **BDD avec profils** : Tests in-memory (rapides) et tests avec persistance (intégration)
- **UUID** : FleetId utilise ramsey/uuid pour des identifiants robustes en production
