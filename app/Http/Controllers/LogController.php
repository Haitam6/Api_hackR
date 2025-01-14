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
     * @OA\Post(
     *     path="/api/getLastLogs",
     *     summary="Obtenir les derniers logs",
     *     tags={"Logs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *        name="nbLogs",
     *       in="query",
     *      required=true,
     *    description="Nombre de logs à récupérer",
     *      @OA\Schema(type="integer")
     * ),
     * 
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Liste des logs"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors de la récupération des logs"
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
            return response()->json(['error' => 'Vous n\'avez pas les droits pour accéder à cette ressource'], 403);
        }

        try {
            $logs = Log::all()->sortByDesc('date_action')->take($request->nbLogs);

            foreach ($logs as $log) {
                $fonctionnalite = Fonctionnalites::find($log->fonctionnalite_id);
                $user = User::find($log->user_id);

                $log->user = $user ? $user->nom : 'Utilisateur inconnu';
                $log->fonctionnalite = $fonctionnalite ? $fonctionnalite->nom_fonctionnalite : 'Fonctionnalité inconnue';
            }

            return response()->json($logs);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des logs ' . $e], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/getLogsByUser",
     *     summary="Obtenir les logs par utilisateur",
     *     tags={"Logs"},
     * security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="utilisateur@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des logs de l'utilisateur"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors de la récupération des logs"
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
            return response()->json(['error' => 'Vous n\'avez pas les droits pour accéder à cette ressource'], 403);
        }

        try {
            $utilisateurRecherche = User::where('email', $request->email)->first();

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
     * @OA\Post(
     *     path="/api/getLogsByFunctionality",
     *     summary="Obtenir les logs par fonctionnalité",
     *     tags={"Logs"},
     * security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nom_fonctionnalite", type="string", example="Consultation des logs")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des logs pour la fonctionnalité"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Fonctionnalité non trouvée"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors de la récupération des logs"
     *     )
     * )
     */
    public function getLogsByFunctionality(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        if ($user->role_id != 1) {
            return response()->json(['error' => 'Vous n\'avez pas les droits pour accéder à cette ressource'], 403);
        }

        try {
            $fonctionnaliteRecherche = Fonctionnalites::where('nom_fonctionnalite', $request->nom_fonctionnalite)->first();

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
