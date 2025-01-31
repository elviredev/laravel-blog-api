<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
  /**
   * @desc Ajouter un like sur un article
   * @route POST /api/add/like
   * @param Request $request
   * @return JsonResponse
   */
  public function addLike(Request $request): JsonResponse
  {
    // valider les données reçues avant de les enregistrer en bdd
    $validatedData = Validator::make($request->all(), [
      // Vérifier si le post_id existe dans table 'posts'
      'post_id' => 'required|integer|exists:posts,id'
    ]);

    if ($validatedData->fails()) {
      return response()->json([
        'errors' => $validatedData->errors(),
      ], 422);
    }

    try {
      $userId = $request->user()->id;
      $postId = $request->post('post_id');
      // Vérifier si l'utilisateur a déjà liké cet article
      $userLikedPostBefore = Like::where('user_id', $userId)
        ->where('post_id', $postId)
        ->exists(); // exists() plutôt que first() si pas besoin de récupérer des données

      if ($userLikedPostBefore) {
        return response()->json([
          'message' => 'You have already liked this post.',
        ], 400);
      } else {
        // Ajouter le like
        Like::create([
          'user_id' => $userId,
          'post_id' => $postId
        ]);

        return response()->json([
          'message' => 'Post liked successfully',
        ], 201);
      }
    } catch (Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  /**
   * @desc Retirer un like si l'utilisateur a déjà aimé un article
   * @route DELETE /api/like
   * @param Request $request
   * @return JsonResponse
   */
  public function removeLike(Request $request): JsonResponse
  {
    // Validation des données
    $validatedData = Validator::make($request->all(), [
      // Vérifier si le post_id existe dans table 'posts'
      'post_id' => 'required|integer|exists:posts,id'
    ]);

    if ($validatedData->fails()) {
      return response()->json(['errors' => $validatedData->errors(),], 422);
    }

    try {
      // Récupérer l'utilisateur connecté
      $user = auth()->user();
      $postId = $request->input('post_id');

      // Vérifier si l'utilisateur a déja liké cet article
      $like = Like::where('user_id', $user->id)->where('post_id', $postId)
        ->first();
      // Si aucun like trouvé
      if (!$like) {
        return response()->json(['message' => 'You have not liked this post.'], 400);
      }

      // Supprimer le like
      $like->delete();
      return response()->json(['message' => 'Like removed successfully',], 200);

    } catch (Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }
}
