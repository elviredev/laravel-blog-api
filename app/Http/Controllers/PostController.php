<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function PHPUnit\Framework\isNumeric;

class PostController extends Controller
{
  /**
   * @desc Ajouter un nouvel article
   * @route POST /api/add/post
   * @param Request $request
   * @return JsonResponse
   */
  public function addNewPost(Request $request): JsonResponse
  {
    // valider les données reçues avant de les enregistrer en bdd
    $validatedData = Validator::make($request->all(), [
      'title' => 'required|string',
      'slug' => 'nullable|string|unique:posts,slug',
      'description' => 'required|string',
    ]);

    if ($validatedData->fails()) {
      return response()->json([
        'errors' => $validatedData->errors(),
      ], 422);
    }

    // Générer le slug à partir du title si non fourni
    $slug = $request->input('slug') ?: Str::slug($request->input('title'));

    // Vérifier l'unicité du slug et en générer un nouveau si nécessaire
    $originalSlug = $slug;
    $counter = 1;
    // Si un slug existe déjà, un compteur est ajouté à la fin du slug (titre, titre-1, titre-2, etc.)
    while (Post::where('slug', $slug)->exists()) {
      $slug = $originalSlug . '-' . $counter++;
    }

    // Ajouter l'article
    $post = Post::create([
      'title' => $request->title,
      'slug' => $slug,
      'description' => $request->description,
      'user_id' => auth()->id(),
    ]);


    // Charger la relation 'user'
    $post->load('user');

    // Retour de la ressource
    return (new PostResource($post))
      ->additional(['message' => 'Post added successfully.'])
      ->response()
      ->setStatusCode(201);
  }

  /**
   * @desc mettre à jour un article
   * @route POST /api/edit/post/{slug}-{post_id}
   * @param Request $request
   * @param string $slug
   * @param int $post_id
   * @return JsonResponse
   */
  public function editPost(Request $request, string $slug, int $post_id):
  JsonResponse
  {
    // Valider les paramètres de l'URL
    if (empty($slug) || empty($post_id)) {
      return response()->json(['message' => 'Parameters missing', 'slug' => $slug, 'post_id' => $post_id], 400);
    }
    if (!isNumeric($post_id)) {
      return response()->json(['message' => 'Invalid post_id. It must be an integer.'], 400);
    }

    // valider les données reçues avant de les enregistrer en bdd
    $validatedData = Validator::make($request->all(), [
      'title' => 'required|string',
      'description' => 'required|string',
      'slug' => 'nullable|string|unique:posts,slug,' . $post_id, // vérifie l'unicité du slug sauf pour cet article
    ]);

    if ($validatedData->fails()) {
      return response()->json([
        'errors' => $validatedData->errors(),
      ], 422);
    }

    // Récupérer les détails de l'article
    $post = Post::where('id', $post_id)->where('slug', $slug)->first();
    if (!$post) {
      return response()->json(['message' => 'Post not found.'], 404);
    }

    // Utilise le slug de la requête ou conserve celui existant
    $newSlug = $request->input('slug', $post->slug);
    // Si le slug a été modifié, on le met à jour, sinon, on garde celui d'origine
    if ($newSlug !== $post->slug) {
      $post->slug = $newSlug;
    }

    // Mettre à jour les autres données
    $post->title = $request->title;
    $post->description = $request->description;

    // Sauvegarder les modifications
    $post->save();

    // Retour de la ressource
    return (new PostResource($post))
      ->additional(['message' => 'Post updated successfully.'])
      ->response()
      ->setStatusCode(200);
  }

  /**
   * @desc Récupérer tous les articles
   * @route GET /api/posts
   * @return JsonResponse
   */
  public function getAllPosts(): JsonResponse
  {
    try {
      // on utilise une collection de modèles
      $posts = Post::with('comments')
        ->withCount('likes', 'comments')
        ->get();
      // Transformer la collection de posts en une collection de ressources. Chaque ressource est ensuite gérée par PostResource
      $resource = PostResource::collection($posts);
      return $resource->response()->setStatusCode(200);
    } catch (Exception $e) {
      return response()->json(['error' => $e->getMessage()], 404);
    }
  }

  /**
   * @desc Récupérer un seul article avec ses commentaires et ses likes
   * @route GET /api/posts/{post_id}
   * @param $post_id
   * @return JsonResponse
   */
  public function getPost($post_id): JsonResponse
  {
    try {
      // Récupération du post et de ses commentaires ou levée d'une exception si introuvable
      $post = Post::with('comments')
        ->withCount('likes', 'comments')
        ->findOrFail($post_id);

      return (new PostResource($post))
        ->response()
        ->setStatusCode(200);
    } catch(ModelNotFoundException $e) {
      return response()->json([
        'error' => "Post not found"
      ], 404);
    } catch(Exception $e) {
      return response()->json([
        'error' => 'An error occurred while fetching the post.',
        'details' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * @desc Supprimer un article
   * @route DELETE /api/posts/{post_id}
   * @param $post_id
   * @return JsonResponse
   */
  public function deletePost($post_id): JsonResponse
  {
    try {
      // Récupération du post ou levée d'une exception si introuvable
      $post = Post::findOrFail($post_id);

      // Suppression du post
      $post->delete();

      return response()->json([
        'message' => 'Post deleted successfully.'
      ], 200);
    } catch(ModelNotFoundException $e) {
      // Cas où le post n'existe pas
      return response()->json([
        'error' => "Post not found"
      ], 404);
    } catch(Exception $e) {
      // Cas d'une erreur générale
      return response()->json([
        'error' => 'An error occurred while deleting the post.',
        'details' => $e->getMessage()
      ], 500);
    }
  }

}
