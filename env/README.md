# Guide d'installation du projet BTSPlay

Ce projet utilise Docker pour configurer un environnement de développement comprenant PHP, MySQL, et phpMyAdmin. Suivez les étapes ci-dessous pour configurer et démarrer le projet sur votre machine.

## Prérequis

Avant de commencer, assurez-vous que vous avez installé les éléments suivants sur votre machine :

- **Docker** : Pour télécharger et installer Docker, rendez-vous sur [Docker Desktop](https://www.docker.com/get-started).
- **Git** : Git est nécessaire pour récupérer le code source du projet depuis GitHub. Si ce n'est pas encore fait, installez-le depuis [Git](https://git-scm.com/downloads).

## Étapes d'installation

### 1. Créer un dossier de projet

Tout d'abord, créez un dossier vide où vous souhaitez configurer votre projet. Par exemple :

```bash
mkdir mon_projet
cd mon_projet
```

### 2. Télécharger le fichier docker-compose.yml
Téléchargez le fichier docker-compose.yml fourni dans la ressource et placez-le dans ce dossier.

### 3. Créer les dossiers nécessaires
Dans le dossier du projet, créez les sous-dossiers suivants pour organiser les fichiers et les données :

```bash
Copier
Modifier
mkdir NAS_ARCH NAS_PAD NAS_MPEG PHP
```
Ces dossiers serviront à stocker des fichiers de différents types de données et à configurer les services Docker correctement.

### 4. Initialiser le dépôt Git
Dans le dossier PHP, ouvrez Git Bash et initialisez un dépôt Git. Ensuite, récupérez le code source du projet depuis le dépôt GitHub :

```bash
Copier
Modifier
cd PHP
git init
git remote add bts https://github.com/Yxshad/BTSAudiovisuel.git
git pull bts
```
Cette commande va télécharger les fichiers du dépôt.

### 5. Lancer Docker
Lancez Docker Desktop et assurez-vous qu'il fonctionne correctement. Docker devrait être en cours d'exécution avant de continuer à la prochaine étape.

### 6. Construire et démarrer les services Docker
Dans le dossier où se trouve le fichier `docker-compose.yml`, exécutez les commandes suivantes pour construire les images Docker et démarrer les services :

```bash
docker-compose build --no-cache
docker compose up -d
```
La commande docker-compose build `--no-cache` construit les images sans utiliser les caches précédents, et docker compose up -d démarre les services en mode détaché.

### 7. Accéder à l'application
Une fois les services Docker démarrés, vous pouvez accéder à l'application à travers les adresses suivantes dans votre navigateur :

```bash
PHP : http://localhost:8000
phpMyAdmin : http://localhost:8082
```
### 8. Connexion à phpMyAdmin
Pour accéder à la base de données via phpMyAdmin, utilisez les identifiants suivants :
```bash
Utilisateur : myuser
Mot de passe : mypassword
```
Ces identifiants permettent de vous connecter à la base de données MySQL du projet.

## Commandes Docker utiles
Voici quelques commandes Docker pratiques pour gérer le projet :

Construire l'image sans cache :

```bash
docker-compose build --no-cach
```
Démarrer les services en mode détaché :

```bash
docker-compose up -d
```
Arrêter les services :

```bash
docker-compose down
```
Vérifier les logs des conteneurs :

```bash
docker-compose logs
```
Accéder à un conteneur en cours d'exécution :

```bash
docker exec -it <nom_du_conteneur> bash
```

## À propos
Ce projet est destiné à fournir un environnement de développement configuré avec Docker pour le projet BTSAudiovisuel. Il comprend des services pour exécuter des applications PHP et gérer une base de données MySQL via phpMyAdmin.

## Auteurs
- CONGUISTI Nicolas
- LAVERGNE Elsa
- LORIDANT Julien
- MARRIER Axel
- MARTIN Solène


Bon travail et bienvenue sur le projet !