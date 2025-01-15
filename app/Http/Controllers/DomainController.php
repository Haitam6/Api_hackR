<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Log;
use App\Models\Droit;
use App\Models\Fonctionnalites;
use App\Models\Role;


class DomainController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/domains/{domain}/subdomains",
     *     summary="Retrieve subdomains of a given domain",
     *     tags={"Fonctionnalités"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="domain",
     *         in="path",
     *         required=true,
     *         description="The domain name to fetch subdomains for.",
     *         @OA\Schema(
     *             type="string",
     *             example="example.com"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of subdomains retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="subdomains",
     *                 type="array",
     *                 @OA\Items(type="string", example="sub.example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="You are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access forbidden due to insufficient permissions.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Vous n'avez pas le droit pour faire cela.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subdomains could not be retrieved.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Impossible de récupérer les sous-domaines.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Erreur interne du serveur."),
     *             @OA\Property(property="details", type="string", example="Specific error details here.")
     *         )
     *     )
     * )
     */
    function verifRoles($fonctionnalite_id, $current_role_id)
{
    try {
        $droit = Droit::where('fonctionnalite_id', $fonctionnalite_id)
                    ->where('role_id', $current_role_id)
                    ->first();

        if (!$droit) {
        
            Droit::create([
                'fonctionnalite_id' => $fonctionnalite_id,
                'role_id' => $current_role_id,
                'droit' => 1, 
            ]);
            return true;
        }

        return true; 
    } catch (\Exception $e) {
        return false;
    }
}

    public function getSubdomains($domain)
    {
        $fonctionnalite_id = 7;
        $user = auth()->user();
        if (!Auth::check()) {
            return response()->json(['error' => 'You are not authentified'], 401);
        }

        if (!$this->verifRoles($fonctionnalite_id, $user->role_id)) {
            return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela.'], 401);
        }

        // API Key setup
        $apiKey = env('SECURITYTRAILS_API_KEY');
        $url = "https://api.securitytrails.com/v1/domain/{$domain}/subdomains";

        try {
            $response = Http::withHeaders(['APIKEY' => $apiKey])->get($url);

            $this->logAction(Auth::id(), 'Subdomains search', 7);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json(['error' => 'Impossible de récupérer les sous-domaines.'], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur interne du serveur.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function logAction($userId, $action, $actionId)
    {
        if ($userId) {
            Log::create([
                'date' => now(),
                'action' => $action,
                'fonctionnalite_id' => $actionId,
                'id_user' => $userId,
            ]);
        }
    }
}
