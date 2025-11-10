# Step 3 – Code Quality & CI/CD

## Outils de qualité du code recommandés

### 1. PHPStan (niveau 7-8)

**Pourquoi ?**
- Analyse statique du code PHP pour détecter les erreurs avant l'exécution
- Cohérent avec notre typage strict (`declare(strict_types=1)`)
- Détecte les incohérences de types, méthodes inexistantes, propriétés non définies
- Niveau 7-8 recommandé pour maximiser la détection d'erreurs potentielles

**Installation :**
```bash
composer require --dev phpstan/phpstan
```

**Configuration minimale (`phpstan.neon`) :**
```yaml
parameters:
    level: 7
    paths:
        - src
    excludePaths:
        - src/Infra/Entity
```

**Utilisation :**
```bash
vendor/bin/phpstan analyse
```

---

### 2. PHP CS Fixer

**Pourquoi ?**
- Garantit un style de code homogène dans toute l'équipe
- Applique automatiquement les standards PSR-12
- Évite les débats sur le formatage du code en code review
- Assure la lisibilité et la maintenabilité

**Installation :**
```bash
composer require --dev friendsofphp/php-cs-fixer
```

**Configuration minimale (`.php-cs-fixer.php`) :**
```php
<?php
return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()->in(__DIR__ . '/src')
    );
```

**Utilisation :**
```bash
vendor/bin/php-cs-fixer fix --dry-run --diff  # Vérification
vendor/bin/php-cs-fixer fix                    # Application
```

---

### 3. Composer Audit (intégré à Composer 2.4+)

**Pourquoi ?**
- Détecte les vulnérabilités de sécurité connues dans les dépendances
- Gratuit et intégré nativement à Composer
- Essentiel pour la sécurité applicative

**Utilisation :**
```bash
composer audit
```

---

### 4. Behat (déjà en place)

**Pourquoi ?**
- Tests BDD qui valident les scénarios métier
- Documentation vivante du comportement attendu
- Deux profils (in-memory + persistence) pour tests rapides et tests d'intégration

---

## Process CI/CD recommandé

### Pipeline GitHub Actions / GitLab CI

```yaml
# .github/workflows/ci.yml
name: CI

on: [push, pull_request]

jobs:
  quality:
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_PASSWORD: postgres
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: pdo_pgsql, intl
      
      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist
      
      - name: Check security vulnerabilities
        run: composer audit
      
      - name: Run PHPStan
        run: vendor/bin/phpstan analyse
      
      - name: Check code style
        run: vendor/bin/php-cs-fixer fix --dry-run --diff
      
      - name: Setup database
        run: |
          php bin/console doctrine:database:create --env=test
          php bin/console doctrine:migrations:migrate --no-interaction --env=test
        env:
          DATABASE_URL: postgresql://postgres:postgres@postgres:5432/fleet_test
      
      - name: Run BDD tests (in-memory)
        run: vendor/bin/behat
      
      - name: Run BDD tests (persistence)
        run: vendor/bin/behat -p persistence
        env:
          DATABASE_URL: postgresql://postgres:postgres@postgres:5432/fleet_test
```

### Étapes du pipeline CI/CD

**1. Installation des dépendances**
```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
```

**2. Audit de sécurité**
```bash
composer audit
```
→ Détecte les CVE dans les dépendances

**3. Analyse statique**
```bash
vendor/bin/phpstan analyse
```
→ Détecte les erreurs de typage et de logique

**4. Vérification du style**
```bash
vendor/bin/php-cs-fixer fix --dry-run --diff
```
→ Vérifie la conformité PSR-12

**5. Tests BDD (rapides)**
```bash
vendor/bin/behat
```
→ Tests in-memory (< 1 seconde)

**6. Tests BDD (intégration)**
```bash
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --no-interaction --env=test
vendor/bin/behat -p persistence
```
→ Tests avec PostgreSQL réel

**7. (Optionnel) Déploiement**
- Génération d'artefact (PHAR, Docker image)
- Déploiement sur environnement de staging
- Notification Slack/Teams du succès/échec

---

## Approche et principes

### Pourquoi ces choix ?

1. **Pas de sur-ingénierie** : outils standards de l'écosystème PHP/Symfony
2. **Typage fort natif** : `declare(strict_types=1)` + `readonly` suffisent, pas besoin d'annotations lourdes
3. **Tests BDD suffisants** : valident les règles métier, pas besoin de tests unitaires supplémentaires pour ce scope
4. **Architecture DDD/CQRS pure** : séparation claire des responsabilités (App/Domain/Infra)
5. **Double implémentation repositories** : in-memory pour rapidité, PostgreSQL pour intégration

### Ce qui n'est PAS inclus (volontairement)

- ❌ Tests unitaires (les tests BDD couvrent déjà les règles métier)
- ❌ Mutation testing (overkill pour ce scope)
- ❌ Coverage à 100% (pragmatisme > dogme)
- ❌ Annotations DocBlock redondantes (typage natif suffisant)
- ❌ Outils complexes (SonarQube, etc.) pour un exercice

---

## Résumé : commandes à retenir

```bash
# Qualité du code
composer audit                                    # Sécurité
vendor/bin/phpstan analyse                        # Analyse statique
vendor/bin/php-cs-fixer fix --dry-run --diff     # Style de code

# Tests
vendor/bin/behat                                  # BDD in-memory
vendor/bin/behat -p persistence                   # BDD PostgreSQL

# Pipeline CI complet
composer install --no-interaction --prefer-dist && \
composer audit && \
vendor/bin/phpstan analyse && \
vendor/bin/php-cs-fixer fix --dry-run --diff && \
vendor/bin/behat && \
vendor/bin/behat -p persistence
```

---

## Pour aller plus loin (hors scope)

Si le projet évoluait vers la production :
- **Monitoring** : Sentry, New Relic pour tracer les erreurs
- **Logs structurés** : Monolog avec contexte métier
- **Métriques** : Prometheus pour observer les performances
- **Tests de charge** : k6, Gatling pour valider la scalabilité
- **Documentation API** : OpenAPI/Swagger si exposition HTTP

