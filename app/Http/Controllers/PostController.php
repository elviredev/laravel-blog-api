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
      'category_id' => 'nullable|integer|exists:categories,id',
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
      'category_id' => $request->category_id
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
      'category_id' => 'nullable|exists:categories,id',
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
    $post->category_id = $request->category_id;

    // Sauvegarder les modifications
    $post->save();

    // Retour de la ressource
    return (new PostResource($post))
      ->additional(['message' => 'Post updated successfully.'])
      ->response()
      ->setStatusCode(200);
  }

  /**
   * @desc Récupérer tous les articles avec les relations et applique la
   * recherche si un mot-clé est fourni. Inclure pagination.
   * @route GET /api/posts
   * @param Request $request
   * @return JsonResponse
   */
  public function getAllPosts(Request $request): JsonResponse
  {
    try {
      // Récupère le mot-clé de recherche
      $query = $request->input('q');

      $posts = Post::with('comments')
        ->withCount('likes', 'comments')
        // Ajouter un filtre pour rechercher le mot-clé dans `title` et `description`
        ->when($query, function ($queryBuilder) use ($query) {
            return $queryBuilder->where('title', 'LIKE', "%$query%")
                                ->orWhere('description', 'LIKE', "%$query%");
        })
        ->paginate(3);

      $resource = PostResource::collection($posts);
      return response()->json([
        // garder les posts formatés dans la response
        'data' => $resource,
        // simplifier la response pour la pagination
        'pagination' => [
          'total' => $posts->total(),
          'per_page' => $posts->perPage(),
          'current_page' => $posts->currentPage(),
          'last_page' => $posts->lastPage(),
          'prev_page_url' => $posts->previousPageUrl(),
          'next_page_url' => $posts->nextPageUrl(),
        ]
      ])->setStatusCode(200);
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

  /**
   * @desc Filtrer les articles par catégorie
   * @route GET /api/categories/{category_id}/posts
   * @param $category_id
   * @return JsonResponse
   */
  public function getPostsByCategory($category_id): JsonResponse
  {
    $posts = Post::where('category_id', $category_id)->paginate(5);

    if ($posts->isEmpty()) {
      return response()->json(['message' => 'No posts found for this category'], 404);
    }

    $resource = PostResource::collection($posts);
    return response()->json([
      'data' => $resource,
      'pagination' => [
        'total' => $posts->total(),
        'per_page' => $posts->perPage(),
        'current_page' => $posts->currentPage(),
        'last_page' => $posts->lastPage(),
        'prev_page_url' => $posts->previousPageUrl(),
        'next_page_url' => $posts->nextPageUrl(),
      ]
    ])->setStatusCode(200);
  }

}
