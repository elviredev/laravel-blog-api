<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
  /**
   * @desc Supprimer un tag
   * @route DELETE /api/tags/{tag}
   * @param $tag_id
   * @return JsonResponse
   */
  public function deleteTag($tag_id): JsonResponse
  {
    $tag = Tag::find($tag_id);

    // Vérifier si le tag existe
    if (!$tag) {
      return response()->json(['message' => 'Tag not found.'], 404);
    }

    // Vérifier si le tag est attaché à des articles
    if ($tag->posts()->exists()) {
      return response()->json(['message' => 'Cannot delete tag. It is linked to posts.'], 400);
    }
    $tag->delete();

    return response()->json(['message' => 'Tag deleted successfully.'], 200);
  }

  /**
   * @desc Retourne tous les articles liés à un tag
   * @route GET /api/tags/{tag}/posts
   * @param $tagName
   * @return JsonResponse
   */
  public function getPostsByTag($tagName): JsonResponse
  {
    // Rechercher le tag par son nom
    $tag = Tag::where('name', $tagName)->first();
    if (!$tag) {
      return response()->json(['message' => 'Tag not found.'], 404);
    }

    // Récupère les articles liés au tag
    $posts = $tag->posts()->with('user', 'category', 'tags')->get();

    // Vérifier s'il y a des articles liés
    if ($posts->isEmpty()) {
      return response()->json([
        'tags' => $tag->name,
        'total_posts' => 0,
        'message' => 'No posts found for this tag.'
      ], 200);
    }

    return response()->json([
      'tags' => $tag->name,
      'total_posts' => $posts->count(),
      // PostResource utilisé pour formater la réponse
      'posts' => PostResource::collection($posts)
    ], 200);
  }
}
