<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use App\Models\Droit;
use App\Models\Fonctionnalites;
use App\Models\Role;

class CrawlerController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/crawlerInformation",
     *     summary="Retrieve information using a search query",
     *     description="Fetches search results using an external API and logs the action.",
     *     tags={"Fonctionnalités"},
     *    security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="search",
     *                 type="string",
     *                 description="Search query for the crawler.",
     *                 example="My digital school"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results returned successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="result",
     *                 type="object",
     *                 description="The search results."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="You are not authenticated"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="An error occurred: [error details]"
     *             )
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
                return false; 
            }

            return true; 
        } catch (\Exception $e) {
            return false;
        }
    }

    public function crawlerInformation(Request $request)
{
    $fonctionnalite_id = 12;
    $user = Auth::user();

    if (!Auth::check()) {
        return response()->json(['error' => 'You are not authenticated'], 401);
    }
    
    if (!$this->verifRoles($fonctionnalite_id, $user->role_id)) {
        return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela.'], 401);
    }

    $validatedData = $request->validate([
        'search' => 'required|string',
    ]);

    $searchQuery = $validatedData['search'];
    $apiKey = env('SERP_API_KEY'); 

    $client = new \GuzzleHttp\Client();
    $url = "https://serpapi.com/search";

    try {
        $response = $client->get($url, [
            'query' => [
                'q' => $searchQuery,  
                'api_key' => $apiKey, 
            ],
        ]);

        $result = json_decode($response->getBody(), true);

        $this->logAction($user->id, "crawler_information", 12);
      

        return response()->json(['result' => $result], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}

private function logAction($userId, $action, $actionId)
    {
        Log::create([
            'date' => now(),
            'action' => $action,
            'fonctionnalite_id' => $actionId,
            'id_user' => $userId,
        ]);
    }

}
