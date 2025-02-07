#!/bin/bash
set -e

echo "Démarrage de l'initialisation Symfony..."

# Attendre que la base de données soit disponible
echo "Vérification de la disponibilité de la base de données..."
wait-for-database database

# Installer les dépendances
echo "Installation des dépendances Composer..."
composer install

# Créer la base de données si elle n'existe pas déjà
echo "Création de la base de données..."
php bin/console doctrine:database:create --if-not-exists

# Générer les migrations
echo "Génération des migrations..."
php bin/console make:migration --no-interaction

# Exécuter les migrations
echo "Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Charger les fixtures
echo "Chargement des fixtures..."
php bin/console doctrine:fixtures:load --no-interaction

#  Execution de la synchronisation des données Firebase
echo "Synchronisation des données Firebase..."
php bin/console app:firebase-sync

## Démarrer Apache
echo "Lancement du serveur Apache..."
exec apache2-foreground
