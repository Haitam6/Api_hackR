<?php

namespace App\Http\Controllers;

use App\Models\Fonctionnalites;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/logs/recent",
     *     summary="Get the last logs",
     *     tags={"Logs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="nbLogs",
     *         in="query",
     *         required=false,
     *         description="Number of logs to retrieve",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of the last logs",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="date_action", type="string", format="date-time", example="2025-01-14T15:04:05"),
     *                 @OA\Property(property="user", type="string", example="Haitam Elqassimi"),
     *                 @OA\Property(property="fonctionnalite", type="string", example="Email spam")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Utilisateur non authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Vous n'avez pas le droit pour faire cela, seul l'admin peut le faire.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error while retrieving logs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Erreur lors de la récupération des logs")
     *         )
     *     )
     * )
     */
    public function getLastLogs(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        if ($user->role_id != 1) {
            return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela, seul l\'admin peut le faire.'], 403);
        }

        try {
            $nbLogs = $request->query('nbLogs', 10);
            $logs = Log::all()->sortByDesc('date_action')->take($nbLogs);

            foreach ($logs as $log) {
                $fonctionnalite = Fonctionnalites::find($log->fonctionnalite_id);
                $user = User::find($log->user_id);

                $log->user = $user ? $user->nom : 'Utilisateur inconnu';
                $log->fonctionnalite = $fonctionnalite ? $fonctionnalite->nom_fonctionnalite : 'Fonctionnalité inconnue';
            }

            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des logs: ' . $e->getMessage()], 500);
        }
    }

     /**
 * @OA\Get(
 *     path="/api/logs/user",
 *     summary="Get logs by user email",
 *     tags={"Logs"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="email",
 *         in="query",
 *         description="Email of the user to retrieve logs for",
 *         required=true,
 *         @OA\Schema(type="string", example="user@example.com")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of logs for the user",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="date_action", type="string", format="date-time", example="2025-01-14T15:04:05"),
 *                 @OA\Property(property="user", type="string", nullable=true, example="Haitam Elqassimi"),
 *                 @OA\Property(property="fonctionnalite", type="string", nullable=true, example="Email spam")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Email parameter is missing",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Paramètre manquant")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="User not authenticated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Utilisateur non authentifié")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Access denied",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Vous n'avez pas le droit pour faire cela, seul l'admin peut le faire.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Utilisateur non trouvé")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error while retrieving logs",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Erreur lors de la récupération des logs")
 *         )
 *     )
 * )
 */

public function getLogsByUser(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Utilisateur non authentifié'], 401);
    }

    if ($user->role_id != 1) {
        return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela, seul l\'admin peut le faire.'], 403);
    }

    try {
        // Récupérer l'email depuis les Query Parameters
        $email = $request->query('email');

        if (!$email) {
            return response()->json(['error' => 'Paramètre "email" manquant'], 400);
        }

        $utilisateurRecherche = User::where('email', $email)->first();

        if (!$utilisateurRecherche) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $logs = Log::where('id_user', $utilisateurRecherche->id)->get();

        foreach ($logs as $log) {
            $fonctionnalite = Fonctionnalites::find($log->fonctionnalite_id);
            $user = User::find($log->user_id);

            $log->user = $user ? $user->nom : 'Utilisateur inconnu';
            $log->fonctionnalite = $fonctionnalite ? $fonctionnalite->nom_fonctionnalite : 'Fonctionnalité inconnue';
        }

        return response()->json($logs);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors de la récupération des logs: ' . $e->getMessage()], 500);
    }
}


 /**
 * @OA\Get(
 *     path="/api/logs/functionality/{nom_fonctionnalite}",
 *     summary="Get logs by functionality",
 *     tags={"Logs"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="nom_fonctionnalite",
 *         in="path",
 *         description="Name of the functionality to retrieve logs for",
 *         required=true,
 *         @OA\Schema(type="string", example="Email spam")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of logs for the functionality",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="date_action", type="string", format="date-time", example="2025-01-14T15:04:05"),
 *                 @OA\Property(property="user", type="string", example="Haitam Elqassimi"),
 *                 @OA\Property(property="fonctionnalite", type="string", example="Email spam")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="User not authenticated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Utilisateur non authentifié")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Access denied",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Vous n'avez pas le droit pour faire cela, seul l'admin peut le faire.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Functionality not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Fonctionnalité non trouvée")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error while retrieving logs",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Erreur lors de la récupération des logs")
 *         )
 *     )
 * )
 */
public function getLogsByFunctionality($nomFonctionnalite)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Utilisateur non authentifié'], 401);
    }

    if ($user->role_id != 1) {
        return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela, seul l\'admin peut le faire.'], 403);
    }

    try {
        $fonctionnaliteRecherche = Fonctionnalites::where('nom_fonctionnalite', $nomFonctionnalite)->first();

        if (!$fonctionnaliteRecherche) {
            return response()->json(['error' => 'Fonctionnalité non trouvée'], 404);
        }

        $logs = Log::where('fonctionnalite_id', $fonctionnaliteRecherche->id)->get()->sortByDesc('date_action');

        foreach ($logs as $log) {
            $fonctionnalite = Fonctionnalites::find($log->fonctionnalite_id);
            $user = User::find($log->user_id);

            $log->user = $user ? $user->nom : 'Utilisateur inconnu';
            $log->fonctionnalite = $fonctionnalite ? $fonctionnalite->nom_fonctionnalite : 'Fonctionnalité inconnue';
        }

        return response()->json($logs);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors de la récupération des logs: ' . $e->getMessage()], 500);
    }
}
}
