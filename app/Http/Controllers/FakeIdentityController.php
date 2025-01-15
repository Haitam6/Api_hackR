<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use Faker\Factory as Faker;
use App\Models\Droit;
use App\Models\Fonctionnalites;
use App\Models\Role;


class FakeIdentityController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/identities/fake",
     *     summary="Generate a Fake Identity",
     *     description="Generates a fake identity and returns the generated data.",
     *     operationId="generateFakeIdentity",
     *     tags={"Fonctionnalités"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully generated fake identity",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "name": "Kevin Niel",
     *                 "email": "Niel_kevin@mds.com",
     *                 "address": "5 avenue de la République, 75001 Paris",
     *                 "phone": "+33 6 12 34 56 78"
     *             },
     *             @OA\Property(property="name", type="string", description="Generated fake name"),
     *             @OA\Property(property="email", type="string", description="Generated fake email"),
     *             @OA\Property(property="address", type="string", description="Generated fake address"),
     *             @OA\Property(property="phone", type="string", description="Generated fake phone number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User is not authenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"error": "You are not authenticated"},
     *             @OA\Property(property="error", type="string", description="Error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not have the necessary permissions",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"error": "Vous n'avez pas le droit pour faire cela."},
     *             @OA\Property(property="error", type="string", description="Error message")
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
   
    public function generateFakeIdentity(Request $request)
    {
        $fonctionnalite_id = 9;

        $user = Auth::user();

        if (!Auth::check()) {
            return response()->json(['error' => 'You are not authentified'], 401);
        }

        if (!$this->verifRoles($fonctionnalite_id, $user->role_id)) {
            return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela.'], 401);
        }
        
        $faker = Faker::create();

        // Générer une identité fictive
        $fakeIdentity = [
            'name' => $faker->name,
            'email' => $faker->email,
            'address' => $faker->address,
            'phone' => $faker->phoneNumber,
        ];
        
        $this->logAction($user->id, 'generate_fake_identity', 9);

        return response()->json($fakeIdentity);
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
