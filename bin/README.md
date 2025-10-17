# README - Projet API Bibliothèque

## Informations
- **Nom :** Kevin CHAILLOT                                                                                                                                                                                                               - **Nom :** Nathan BOUCHE
- **Projet :** API REST Bibliothèque avec Symfony

## Introduction
Pour ce projet, j'ai créé une API REST qui permet de gérer une bibliothèque avec des auteurs et des livres. J'ai utilisé Symfony comme framework PHP.

## Installation

J'ai d'abord créé le projet Symfony :
composer create-project symfony/skeleton:"6.4.x" my_project_directory

Ensuite j'ai configuré la base de données dans le fichier .env :
DATABASE_URL="mysql://root:@127.0.0.1:3306/bibliotheque"

Puis j'ai créé la base de données :
php bin/console doctrine:database:create

## Les entités

### Auteur
J'ai créé l'entité Auteur avec la commande make:entity. Elle contient :
- nom (string)
- prenom (string)
- dateNaissance (date, nullable)

Au début j'avais oublié de mettre nullable sur la date de naissance, du coup ça plantait quand je créais un auteur sans date. J'ai corrigé après.

### Livre
Pour l'entité Livre j'ai mis :
- titre (string)
- isbn (string, unique)
- datePublication (date)
- auteur (relation ManyToOne)

La partie relation m'a pris du temps à comprendre. En gros un livre a UN auteur mais un auteur peut avoir PLUSIEURS livres.

Après avoir créé les entités j'ai lancé la migration :
php bin/console make:migration
php bin/console doctrine:migrations:migrate
                                                                                                                                                                                                                                                                      ## mise a jour des  entités                                                                                                                                                                                                        composer create-project symfony/skeleton:"7.3.x" my_project_directory                                                                                                                                        
## Les contrôleurs

J'ai créé deux contrôleurs : AuteurController et LivreController.

Pour chaque contrôleur j'ai fait les 5 routes classiques :
- GET /api/auteurs : liste tous les auteurs
- GET /api/auteurs/{id} : affiche un auteur
- POST /api/auteurs : crée un auteur
- PUT /api/auteurs/{id} : modifie un auteur
- DELETE /api/auteurs/{id} : supprime un auteur

Pareil pour les livres avec /api/livres

### Difficultés rencontrées

Le plus compliqué c'était de récupérer les données JSON dans les méthodes POST et PUT. Au début j'avais oublié de faire $request->getContent() avant le json_decode() donc ça marchait pas.

Pour créer un livre j'ai galéré aussi parce qu'il faut récupérer l'auteur existant avec son ID :
$auteur = $auteurRepository->find($data['auteur_id']);
$livre->setAuteur($auteur);

## Tests avec Postman

J'ai testé toutes mes routes avec Postman.

Exemple pour créer un auteur :
POST http://localhost:8000/api/auteurs
Body :
{
    "nom": "Hugo",
    "prenom": "Victor"
}

Exemple pour créer un livre :
POST http://localhost:8000/api/livres
Body :
{
    "titre": "Les Misérables",
    "isbn": "9781234567890",
    "datePublication": "1862-04-03",
    "auteur_id": 1
}


## Ce que j'ai appris

- Comment créer une API REST avec Symfony
- Utiliser Doctrine pour gérer la base de données
- Faire des relations entre entités (ManyToOne, OneToMany)
- Manipuler du JSON en PHP
- Tester une API avec Postman
- Les différentes méthodes HTTP (GET, POST, PUT, DELETE)

## Lancement du projet

composer install
php bin/console doctrine:database:create
... (6lignes restantes)