# API laravel-blog-api
- Uniquement l'API, le backend
- BDD MySQL

## Création projet

- démarrer XAMPP
- start Apache et MySQL
- ouvrir phpMyAdmin


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










