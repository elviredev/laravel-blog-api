<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
  /**
   * @desc Enregistrer le user, générer un token
   * @route POST /api/register
   * @param Request $request
   * @return JsonResponse
   */
  public function register(Request $request): JsonResponse
  {
    // valider les 3 champs user avant de l'ajouter en bdd
    $validatedData = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8|confirmed',
    ]);

    // vérifier s'il y a une erreur de validation des données
    if ($validatedData->fails()) {
      return response()->json($validatedData->errors(), 403);
    }

    try {
      // si validation OK, enregistrer le new user
      /** @var User $user */
      $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
      ]);

      // Suppression des tokens existants (facultatif si login unique est requis)
      $user->tokens()->delete();

      // générer le token qui sera accessible pour le user
      // laravel sanctum stocke le token en bdd dans une version hashée
      // la version en clair est accessible via plainTextToken (pour le client)
      $token = $user->createToken('auth_token')->plainTextToken;

      // retourner au client (frontend) le token, les infos user et le status
      return response()->json([
        // le token généré en clair, qui sera utilisé par le client pour
        // authentifier les requêtes
        'access_token' => $token,
        'user' => [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email,
        ],
      ], 201);
    } catch(Exception $e) {
      return response()->json(["error" => $e->getMessage()], 500);
    }
  }

  /**
   * @desc Authentifier le user, générer un token
   * @route POST /api/login
   * @param Request $request
   * @return JsonResponse
   */
  public function login(Request $request): JsonResponse
  {
    // valider les 3 champ user avant de l'ajouter en bdd
    $validatedData = Validator::make($request->all(), [
      'email' => 'required|string|email',
      'password' => 'required|string|min:8',
    ]);

    // vérifier s'il y a une erreur de validation des données
    if ($validatedData->fails()) {
      return response()->json(['error' => $validatedData->errors()], 422);
    }

    // Gestion du verrouillage des tentatives de connexion
    $ipAddress = $request->ip();
    if (RateLimiter::tooManyAttempts($ipAddress, 5)) {
      return response()->json(['error' => 'Too many login attempts. Please try again later'], 429);
    }

    // authentifier le user
    try {
      // récupérer les identifiants de connexion dans une var
      $credentials = $request->only(['email', 'password']);
      // si user pas authentifié, tenter d'authentifier avec les credentials
      if (!auth()->attempt($credentials)) {
        // incrémenter les tentatives échouées
        RateLimiter::hit($ipAddress, 60); // Pénalité d'une min pour chaque tentative échouée
        // si c'est un échec, les identifiants obtenus ne sont pas corrects
        return response()->json(['error' => 'Invalid credentials'], 401);
      }

      // si les identifiants sont valides, le user est authentifié automatiquement
      // récupération du user authentifié
      $user = auth()->user();

      // Les tokens existants sont supprimés avant d'en créer un nouveau pour garantir une seule session active par user.
      $user->tokens()->delete();

      // générer un nouveau token pour cet user
      $token = $user->createToken('auth_token')->plainTextToken;

      // Réinitialisation des tentatives échouées
      RateLimiter::clear($ipAddress);

      // retourner le token, les infos user et le status au client (frontend)
      return response()->json([
        'access_token' => $token,
        'user' => [
          'id' => $user->id,
          'name' => $user->name,
          'email' => $user->email
        ]
      ], 200);
    } catch (Exception $e) {
      return response()->json(["error" => $e->getMessage()], 500);
    }
  }

  /**
   * @desc Deconnecter le user authentifié
   * @route POST /api/logout
   * @param Request $request
   * @return JsonResponse
   */
  public function logout(Request $request): JsonResponse
  {
    // supprime le token du user authentifié
    $request->user()->currentAccessToken()->delete();

    return response()->json([
      "message" => "Logged out successfully"
    ], 200);
  }
}
