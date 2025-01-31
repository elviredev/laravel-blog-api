<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CommentController extends Controller
{
  /**
   * @desc Ajouter un commentaire à un post
   * @route POST /api/add/comment
   * @param Request $request
   * @return JsonResponse
   */
  public function addComment(Request $request): JsonResponse
  {
    // valider les données reçues avant de les enregistrer en bdd
    $validatedData = Validator::make($request->all(), [
      // Vérifier si le post_id existe dans table 'posts'
      'post_id' => 'required|integer|exists:posts,id',
      'comment' => 'required|string'
    ]);

    if ($validatedData->fails()) {
      return response()->json([
        'errors' => $validatedData->errors(),
      ], 422);
    }

    try {
      $userId = $request->user()->id;
      $postId = $request->post('post_id');
      $comment = $request->post('comment');
      // Ajouter le comment
      Comment::create([
        'user_id' => $userId,
        'post_id' => $postId,
        'comment' => $comment
      ]);

      return response()->json([
        'message' => 'Comment added successfully',
      ], 201);
    } catch (Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }
}
