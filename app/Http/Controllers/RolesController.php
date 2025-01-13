<?php

namespace App\Http\Controllers;

use App\Models\Droit;
use App\Models\Log;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use App\Models\Fonctionnalites;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;


class RolesController extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/giveRules",
     *     summary="Assign or modify rights for a role",
     *     description="Assigns or modifies the rights (droit) of a role for a specific functionality.",
     *     operationId="giveRules",
     *     tags={"Roles Management"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"fonctionnalite_id", "role_id"},
     *                 @OA\Property(
     *                     property="fonctionnalite_id",
     *                     type="integer",
     *                     description="ID of the functionality",
     *                 ),
     *                 @OA\Property(
     *                     property="role_id",
     *                     type="integer",
     *                     description="ID of the role to be assigned",
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully assigned or modified rights",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="result", type="string", description="Success message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - The right already exists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User does not have the right to assign roles",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Error message")
     *         )
     *     )
     * )
     */
    public function giveRules(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        if($user->role_id != 1){
            return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela.'], 401);
        }

        try{
            $fonctionnalite = Droit::where('fonctionnalite_id', $request->fonctionnalite_id)->first();

            if(!$fonctionnalite){

                $droit = new Droit();
                $droit->fonctionnalite_id = $request->fonctionnalite_id;
                $droit->role_id = $request->role_id;
                $droit->save();

                return response()->json(['result' => 'Vous avez ajouté le droit avec succès'], 200);

            }
            else
            {
                $droit = Droit::where('fonctionnalite_id', $request->fonctionnalite_id)->where('role_id', $request->role_id)->first();
                if($droit){
                    return response()->json(['error' => 'Ce droit existe déjà'], 400);
                }
                else
                {
                    $droit = Droit::where('fonctionnalite_id', $request->fonctionnalite_id)->first();
                    $droit->role_id = $request->role_id;
                    $droit->save();

                    return response()->json(['result' => 'Vous avez modifié le droit avec succès'], 200);

                }
            }

        }
        catch(\Exception $e){
            return response()->json(['error' => 'Erreur'.$e], 500);
        }
    }

}
