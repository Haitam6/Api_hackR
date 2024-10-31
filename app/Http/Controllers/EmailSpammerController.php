<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log as LaravelLog;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;


class EmailSpammerController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/emails/spam",
     *     summary="Spam emails to a recipient",
     *     tags={"Email Spammer"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="recipient_email", type="string", example="recipient@example.com"),
     *             @OA\Property(property="subject", type="string", example="Spam Subject"),
     *             @OA\Property(property="content", type="string", example="This is the spam content."),
     *             @OA\Property(property="count", type="integer", example=10)
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

                // Log the email spam action
                $user = Auth::user();
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
            'action_id' => $actionId,
            'id_user' => $userId,
        ]);
    }
}
