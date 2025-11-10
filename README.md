# Vehicle Fleet Parking Management - Step 1

Application d'exercice DDD/CQRS permettant de gérer l'enregistrement et la localisation de véhicules au sein d'une flotte.

## Step 1: DDD/CQRS Core avec tests BDD et persistance en mémoire

Ce step implémente le cœur du domaine métier sans framework, avec :
- Modèle de domaine riche (entités Fleet, Vehicle, User)
- Value Objects (FleetId, VehicleId, Location, UserId)
- Séparation Commands/Queries (CQRS)
- Tests BDD avec Behat
- Persistance en mémoire uniquement

## Prérequis

- PHP ≥ 8.1
- Composer

## Installation

```bash
composer install
```

## Exécution des tests BDD

```bash
./vendor/bin/behat
```

## Structure du projet

```
src/
  App/            # Commandes, queries et handlers (couche application)
  Domain/         # Modèle métier & Value Objects
  Infra/          # Implémentations de repositories (InMemory uniquement)
features/         # Scénarios Behat (register_vehicle, park_vehicle)
features/bootstrap/  # Contextes Behat
```

## Principes appliqués

- **DDD** : Modèle de domaine riche avec entités (Fleet, Vehicle, User) et Value Objects (FleetId, VehicleId, Location, UserId)
- **CQRS** : Séparation commandes (RegisterVehicle, ParkVehicle) et queries (GetVehicleLocation)
- **Hexagonal Architecture** : Le domaine est isolé, les repositories sont des interfaces
- **BDD** : Tests avec Behat suivant les scénarios définis
- **Pas de framework** : Code pur PHP, aucune dépendance de production

## Scénarios de test

### Register a vehicle
- Enregistrer un véhicule dans une flotte
- Empêcher l'enregistrement en double dans la même flotte
- Permettre à un véhicule d'appartenir à plusieurs flottes

### Park a vehicle
- Garer un véhicule à une location
- Empêcher de garer au même endroit deux fois de suite
