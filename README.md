# Fournisseur d'authentification API Symfony

Ce projet est une application de fournisseur d'authentification avec Symfony. Il utilise Docker pour l'environnement de développement et PostgreSQL pour la base de données.

## Prérequis

- **Docker** : Assurez-vous que Docker et Docker Compose sont correctement installés sur votre machine. Vous pouvez vérifier cela en exécutant :
  ```bash
  docker --version
  docker-compose --version

## Installation

1. Clonez le dépôt :
   ```bash
   git clone https://github.com/DylanQin4/Library-api.git
   cd Library-api
    ```
2. Créez un fichier `.env` à partir du fichier `.env.example` :

    ```bash
    cp .env.example .env
    ```
   et si vous êtes sur un système d'exploitation Windows vous pouvez utiliser la commande `copy` :
    ```bash
    copy .env.example .env
    ```
3. Construisez les conteneurs Docker :

    ```bash
    docker-compose up -d
    ```
   
4. Installez les dépendances PHP :

    ```bash
    docker exec -it library_api_symfony composer install
    ```
   
5. Créez la base de données :

    ```bash
    docker exec -it library_api_symfony php bin/console doctrine:database:create
    ```
   
6. Générez les migrations :

    ```bash
    docker exec -it library_api_symfony php bin/console make:migration
    ```
   
7. Exécutez les migrations :

    ```bash
    docker exec -it library_api_symfony php bin/console doctrine:migrations:migrate
    ```
   
8. Chargez les fixtures :

    ```bash
    docker exec -it library_api_symfony php bin/console doctrine:fixtures:load
    ```
   repondez `yes` pour confirmer le chargement des fixtures.


9. L'API est maintenant accessible à l'adresse `http://localhost:8000`.