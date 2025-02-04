<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// obtenir les infos utilisateur si celui-ci est connecté
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// GET all posts
Route::get('/posts', [PostController::class, 'getAllPosts']);
// GET single post
Route::get('/posts/{post_id}', [PostController::class, 'getPost']);

// GET all categories
Route::get('/categories', [CategoryController::class, 'getAllCategories']);
// Filtrer les posts par catégorie
Route::get('/categories/{category_id}/posts', [PostController::class, 'getPostsByCategory']);

// Pour users authentifiés
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);

  // Endpoints API Post
  Route::post('/add/post', [PostController::class, 'addNewPost']);
  Route::post('/edit/post/{slug}-{post_id}', [PostController::class, 'editPost'])
    ->where([
      'slug' => '[a-z0-9\-]+', // Permet les slugs au format kebab-case
      'post_id' => '[0-9]+'
    ]);
  Route::delete('/posts/{post_id}', [PostController::class, 'deletePost']);

  // Endpoints API Comment
  Route::post('/add/comment', [CommentController::class, 'addComment']);
  Route::post('/add/like', [LikeController::class, 'addLike']);
  Route::delete('/like', [LikeController::class, 'removeLike']);

  // Endpoints API Category
  Route::post('/add/category', [CategoryController::class, 'addCategory']);
  Route::put('/edit/category/{id}', [CategoryController::class, 'editCategory']);
  Route::delete('/categories/{id}', [CategoryController::class, 'deleteCategory']);

  // Delete Image
  Route::delete('/posts/{post_id}/image', [PostController::class, 'deleteImage']);
});
