<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use App\Models\Droit;
use App\Models\Fonctionnalites;
use App\Models\Role;

class DdosController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/tests/ddos",
 *     summary="Simulate a high traffic load on a target server",
 *     description="This feature performs a load test by sending a specified number of HTTP requests to a given URL. The test results include HTTP status codes and response reasons. Authentication and role verification are required.",
 *     tags={"Fonctionnalités"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Input parameters for the load test.",
 *         @OA\JsonContent(
 *             type="object",
 *             required={"url", "requests"},
 *             @OA\Property(
 *                 property="url",
 *                 type="string",
 *                 format="url",
 *                 description="The target URL to load test.",
 *                 example="https://example.com"
 *             ),
 *             @OA\Property(
 *                 property="requests",
 *                 type="integer",
 *                 description="The number of requests to send to the target URL.",
 *                 example=10,
 *                 minimum=1,
 *                 maximum=100
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Load test completed successfully.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 description="Success message.",
 *                 example="Load test completed successfully."
 *             ),
 *             @OA\Property(
 *                 property="results",
 *                 type="array",
 *                 description="Details of each HTTP request sent during the load test.",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(
 *                         property="status",
 *                         type="integer",
 *                         description="HTTP status code of the response.",
 *                         example=200
 *                     ),
 *                     @OA\Property(
 *                         property="reason",
 *                         type="string",
 *                         description="HTTP reason phrase of the response.",
 *                         example="OK"
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="User not authenticated or lacking required permissions.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string",
 *                 description="Error message for unauthorized access.",
 *                 example="You are not authenticated"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error due to missing or invalid input data.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string",
 *                 description="Validation error message.",
 *                 example="The url and requests fields are required."
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error occurred during the load test.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string",
 *                 description="Error message describing the internal server issue.",
 *                 example="An error occurred: Connection timed out"
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
 

    public function DdosTest(Request $request)
    {
        $fonctionnalite_id = 10;
        
        $validatedData = $request->validate([
            'url' => 'required|url',
            'requests' => 'required|integer|min:1|max:100',
        ]);

        $url = $validatedData['url'];
        $requests = $validatedData['requests'];

        
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'You are not authenticated'], 401);
        }

        if (!$this->verifRoles($fonctionnalite_id, $user->role_id)) {
            return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela.'], 401);
        }

        
        $this->logAction($user->id, 'Ddos', 10);

        
        $client = new Client();

        $promises = [];
        for ($i = 0; $i < $requests; $i++) {
            $promises[] = $client->getAsync($url);
        }

        $results = [];
        $responses = Utils::settle($promises)->wait();
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
            'fonctionnalite_id' => $actionId,
            'id_user' => $userId,
        ]);
    }
}
