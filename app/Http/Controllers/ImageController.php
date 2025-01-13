<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use GuzzleHttp\Client;
use App\Models\Droit;
use App\Models\Fonctionnalites;
use App\Models\Role;


class ImageController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/random-image",
 *     summary="Fetch a random image",
 *     tags={"Fonctionnalités"},
 *    security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Random image retrieved successfully",
 *         content={
 *             @OA\MediaType(
 *                 mediaType="image/jpeg",
 *                 schema={
 *                     @OA\Schema(type="string", format="binary")
 *                 }
 *             )
 *         }
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="User not authenticated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="You are not authenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to fetch image",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="error", type="string", example="Failed to fetch the image.")
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

    public function fetchRandomImage()
    {
        $fonctionnalite_id = 11;
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'You are not authenticated.'], 401);
        }

        if (!$this->verifRoles($fonctionnalite_id, $user->role_id)) {
            return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela.'], 401);
        }
        
        $this->logAction($user->id, 'Generate random image', 11);

        try {
            $client = new Client();
            $url = 'https://thispersondoesnotexist.com';

            $response = $client->get($url, ['stream' => true]);

            return response($response->getBody()->getContents(), 200)
                ->header('Content-Type', 'image/jpeg');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch the image.',
            ], 500);
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