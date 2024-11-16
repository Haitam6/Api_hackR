<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Log;

class DomainController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/domains/{domain}",
     *     summary="Retrieve subdomains of a given domain",
     *     tags={"Domains"},
     *     @OA\Parameter(
     *         name="domain",
     *         in="path",
     *         required=true,
     *         description="The domain name to fetch subdomains for",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of subdomains",
     *         @OA\JsonContent(
     *             @OA\Property(property="subdomains", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Domain not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Impossible de récupérer les sous-domaines.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Internal server error.")
     *         )
     *     )
     * )
     */
    public function getSubdomains($domain)
    {
        // Vérifie si l'utilisateur est authentifié
        if (!Auth::check()) {
            return response()->json(['error' => 'You are not authentified'], 401);
        }

        // API Key setup
        $apiKey = env('SECURITYTRAILS_API_KEY');
        $url = "https://api.securitytrails.com/v1/domain/{$domain}/subdomains";

        try {
            // API request to get subdomains
            $response = Http::withHeaders(['APIKEY' => $apiKey])->get($url);

            // Log the action for retrieving subdomains
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
                'action_id' => $actionId,
                'id_user' => $userId,
            ]);
        }
    }
}
