<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
  /**
   * @desc Obtenir toutes les catégories
   * @route GET /api/categories
   * @return JsonResponse
   */
  public function getAllCategories(): JsonResponse
  {
    return response()->json(Category::all(), 200);
  }

  /**
   * @desc Créer une categorie
   * @route POST /api/add/category
   * @param Request $request
   * @return JsonResponse
   */
  public function addCategory(Request $request): JsonResponse
  {
    $validatedData = Validator::make($request->all(), [
      'name' => 'required|string|unique:categories|max:255'
    ]);

    if ($validatedData->fails()) {
      return response()->json([
        'errors' => $validatedData->errors(),
      ], 422);
    }

    try {
      $category = Category::create(['name' => $request->name]);
      return response()->json([
        'message' => 'Category created successfully',
        'category' => $category
      ], 201);
    } catch (Exception $e) {
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  /**
   * @desc Modifier une catégorie
   * @route PUT /api/edit/category/{id}
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function editCategory(Request $request, int $id): JsonResponse
  {
    $validatedData = Validator::make($request->all(), [
      'name' => 'required|string|max:255'
    ]);

    if ($validatedData->fails()) {
      return response()->json([
        'errors' => $validatedData->errors(),
      ], 422);
    }

    $category = Category::find($id);

    if (!$category) {
      return response()->json(['error' => 'Category not found'], 404);
    }

    $category->update(['name' => $request->name]);
    return response()->json([
      'message' => 'Category updated successfully',
      'data' => $category
    ], 200);
  }

  /**
   * @desc Supprimer une catégorie
   * @route DELETE /api/categories/{id}
   * @param int $id
   * @return JsonResponse
   */
  public function deleteCategory(int $id): JsonResponse
  {
    $category = Category::find($id);

    if (!$category) {
      return response()->json(['error' => 'Category not found'], 404);
    }

    $category->delete();
    return response()->json(['message' => 'Category deleted successfully'], 200);
  }
}
