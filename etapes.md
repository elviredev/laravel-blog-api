# API laravel-blog-api
https://www.youtube.com/watch?v=wKQ1IJjmCgk

## API d'authentification basée sur le token
### Fonctionnalités: Register, Login, Logout
- Mise en place de HTTP Client intégrée à PhpStorm pour les requêtes HTTP

## API blog
### Fonctionnalités:
ajouter, modifier et supprimer un article
- récupérer tous les articles
- récupérer un article
- permettre à un user d'ajouter un commentaire pour un article
- permettre à un user de liker un article

### Base de données
- BDD MySQL
- phpMyAdmin

## Création projet

- démarrer XAMPP
- start Apache et MySQL
- quand on lance MySQL via XAMPP et qu'on a une erreur c'est parce que le service mysql.exe a déja été lancé donc 
il faut tuer le processus qui utilise le port 3306 (voir doc C:\sandboxBackend\PHP-MySQL-PHPMYADMIN\MySQL.txt)
- ouvrir phpMyAdmin : http://localhost/phpmyadmin/


- Windows powershell

```bash
>> laravel new laravel-blog-api
  >> none # pas de starter kit
  >> 1 # phpUnit
  
  >> mysql
  >> yes # migration
```

- ouvrir le projet dans phpStorm
- utilisation de **Laravel Sanctum** qui fournit un système d'authentification léger pour les SPA (applications à page unique), les applications mobiles et les API simples basées sur des jetons.
- Sanctum permet à chaque utilisateur de votre application de générer plusieurs jetons API pour son compte. Ces jetons peuvent se voir attribuer des capacités/portées qui spécifient les actions que les jetons sont autorisés à effectuer.
- installer Laravel Sanctum
- cela va installer le fichier `api.php` dans `routes`

```bash
  >> php artisan install:api
    >> yes # migration nouvelle table
```

- `api.php` est créé
- une nouvelle table `personal_access_tokens` est créée
- démarrer le serveur

```bash
  >> php artisan serve
```

## Authentifier les utilisateurs

### register()
- enregistrer les users pour qu'ils puissent se connecter et se déconnecter
- Model `User.php` : ajouter `HasApiTokens` depuis Sanctum
- Créer le controller pour l'authentification

```bash
  >> php artisan make:controller AuthController
```

- mettre en place les fonctionnalités `register` et `login` dans `AuthController`
- créer la fonction register()
- créer la route `POST /register` dans `api.php`


### HTTP Client intégré à PhpStorm
- tester les requêtes HTTP
- utilisation du client HTTP intégré à phpStorm
- créer un fichier `http-request-auth.http`
- raccourcis Http Client 
- ** ctrl + J ** pour avoir les modèles de requêtes
- ** shift + f10 ** pour exécuter la requête
- utiliser ** ### ** pour ajouter une requête


- tester `POST /api/register` : avec données envoyées, sans données et tester validation des données
- vérifier la response retournée : avec le token et les infos user
- vérifier si user créé en bdd : table `user` et `personal_access_token`

### login()
- créer `login()` dans `AuthController`
- créer la route `POST /login`
- tester `POST /api/login`


### logout()
- créer `logout()` dans `AuthController`
- créer la route `GET /logout`
- tester  `POST /api/logout`


## Création API Blog
- ajouter, modifier et supprimer un article
- récupérer tous les articles
- récupérer un article
- permettre à un user d'ajouter un commentaire pour un article
- permettre à un user de liker un article
- pour tester l'api blog, créer un fichier `http-request-blog-api.http`

### Ajouter les tables avec leurs modèles en BDD pour gérer les articles

- cette commande créé le model et la migration de la table
```bash
  >> php artisan make:model Post -m
  >> php artisan make:model Comment -m
  >> php artisan make:model Like -m
```

- créer le controller
```bash
  >> php artisan make:controller PostController
```

- modifier la migration de la table `posts`

















