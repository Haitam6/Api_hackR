<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log as LaravelLog;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Droit;
use App\Models\Fonctionnalites;
use App\Models\Role;


class EmailSpammerController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/emails/spam",
     *     summary="Spam emails to a recipient",
     *     tags={"Fonctionnalités"},
     *    security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="recipient_email", type="string", example="haitam.elqassimi@my-digital-school.org"),
     *             @OA\Property(property="subject", type="string", example="Spam Subject"),
     *             @OA\Property(property="content", type="string", example="This is the spam content."),
     *             @OA\Property(property="count", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Emails sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Emails sent successfully!"),
     *             @OA\Property(property="email_count", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send emails",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to send emails"),
     *             @OA\Property(property="details", type="string", example="Error details here")
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
    
    public function spamEmails(Request $request)
    {
        $validatedData = $request->validate([
            'recipient_email' => 'required|email',
            'subject' => 'required|string',
            'content' => 'required|string',
            'count' => 'required|integer|min:1',
        ]);

        $recipientEmail = $validatedData['recipient_email'];
        $subject = $validatedData['subject'];
        $content = $validatedData['content'];
        $count = $validatedData['count'];

        // Load mail configuration from .env
        config([
            'mail.mailers.smtp.host' => env('MAIL_HOST'),
            'mail.mailers.smtp.port' => env('MAIL_PORT'),
            'mail.mailers.smtp.username' => env('MAIL_USERNAME'),
            'mail.mailers.smtp.password' => env('MAIL_PASSWORD'),
            'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION'),
        ]);

        try {
            for ($i = 0; $i < $count; $i++) {
                Mail::raw($content, function ($message) use ($recipientEmail, $subject) {
                    $message->to($recipientEmail)
                            ->subject($subject);
                });

                $fonctionnalite_id = 5;
                $user = Auth::user();

                if (!$user) {
                    return response()->json(['error' => 'User not authenticated'], 401);
                }

                if (!$this->verifRoles($fonctionnalite_id, $user->role_id)) {
                    return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela.'], 401);
                }
                $this->logAction($user ? $user->id : null, 'email_spam', 5);
            }
            return response()->json([
                'message' => 'Emails sent successfully!',
                'email_count' => $count,
            ]);
        } catch (\Exception $e) {
            LaravelLog::error('Failed to send email: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to send emails',
                'details' => $e->getMessage(),
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
