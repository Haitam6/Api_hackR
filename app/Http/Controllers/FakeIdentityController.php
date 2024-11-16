<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use Faker\Factory as Faker;

class FakeIdentityController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/generate-fake-identity",
     *     summary="Generate a fake identity",
     *     tags={"Identity Generation"},
     *     @OA\Response(
     *         response=200,
     *         description="Fake identity generated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="address", type="string", example="123 Main St, Springfield, USA"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
   
    public function generateFakeIdentity(Request $request)
    {
        // Vérifie si l'utilisateur est authentifié
        if (!Auth::check()) {
            return response()->json(['error' => 'You are not authentified'], 401);
        }
        // Créer une instance de Faker
        $faker = Faker::create();

        // Générer une identité fictive
        $fakeIdentity = [
            'name' => $faker->name,
            'email' => $faker->email,
            'address' => $faker->address,
            'phone' => $faker->phoneNumber,
        ];

        // Récupérer l'utilisateur authentifié
        $user = Auth::user();

        // Enregistrer l'action dans les logs
        $this->logAction($user->id, 'generate_fake_identity', 9);

        // Retourner la réponse JSON
        return response()->json($fakeIdentity);
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
