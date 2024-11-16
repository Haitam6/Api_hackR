<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

class DdosController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/Ddos",
 *     summary="Simulate a high traffic load on a target server",
 *     tags={"Load Test"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"url", "requests"},
 *             @OA\Property(property="url", type="string", format="url", example="https://example.com"),
 *             @OA\Property(property="requests", type="integer", example=10, minimum=1, maximum=100)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Load test completed successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Load test completed successfully."),
 *             @OA\Property(
 *                 property="results",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="status", type="integer", example=200),
 *                     @OA\Property(property="reason", type="string", example="OK")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="User not authenticated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="You are not authenticated")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="The url and requests fields are required.")
 *         )
 *     )
 * )
 */

    public function DdosTest(Request $request)
    {
        // Valider les champs requis
        $validatedData = $request->validate([
            'url' => 'required|url',
            'requests' => 'required|integer|min:1|max:100', // Ajustez la limite si nécessaire
        ]);

        $url = $validatedData['url'];
        $requests = $validatedData['requests'];

        // Authentification requise
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'You are not authenticated'], 401);
        }

        // Log de l'action
        $this->logAction($user->id, 'Ddos', 10);

        // Initialiser Guzzle
        $client = new Client();

        // Générer les promesses pour des requêtes asynchrones
        $promises = [];
        for ($i = 0; $i < $requests; $i++) {
            $promises[] = $client->getAsync($url);
        }

        // Attendre que toutes les promesses soient résolues
        $results = [];
        $responses = Promise\settle($promises)->wait();
        foreach ($responses as $response) {
            if ($response['state'] === 'fulfilled') {
                $results[] = [
                    'status' => $response['value']->getStatusCode(),
                    'reason' => $response['value']->getReasonPhrase(),
                ];
            } elseif ($response['state'] === 'rejected') {
                $results[] = [
                    'status' => $response['reason']->getCode(),
                    'reason' => $response['reason']->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Load test completed successfully.',
            'results' => $results,
        ]);
    }

    private function logAction($userId, $action, $actionId)
    {
        Log::create([
            'date' => now(),
            'action' => $action,
            'action_id' => $actionId,
            'id_user' => $userId,
        ]);
    }
}
