<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
  public function addNewPost(Request $request): JsonResponse
  {
    // valider les données reçues avant de les enregistrer en bdd
    $validatedData = Validator::make($request->all(), [
      'title' => 'required|string',
      'slug' => 'required|string|unique:posts,slug',
      'description' => 'required|string',
    ]);

    if ($validatedData->fails()) {
      return response()->json([
        'errors' => $validatedData->errors(),
      ], 422);
    }

    // Ajouter l'article
    $post = new Post();
    // Remplit les champs depuis $validatedData comme le model "Post" utilise $fillable
    $post->fill($request->only(['title', 'slug', 'description']));
    $post->user_id = auth()->user()->id; // Associe l'utilisateur
    $post->save();

    // Charger la relation 'user'
    $post->load('user');

    // Retour de la ressource
    return (new PostResource($post))
      ->additional(['message' => 'Post added successfully.'])
      ->response()
      ->setStatusCode(201);
  }
}
