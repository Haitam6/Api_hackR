<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log as LaravelLog; // For logging
use App\Models\Log; // If you need logging
use Illuminate\Support\Facades\Auth;

class EmailSpammerController extends Controller
{
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

        // Check if a user is authenticated
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Load mail configuration from .env
        config([
            'mail.mailers.smtp.host' => env('MAIL_HOST'),
            'mail.mailers.smtp.port' => env('MAIL_PORT'),
            'mail.mailers.smtp.username' => env('MAIL_USERNAME'),
            'mail.mailers.smtp.password' => env('MAIL_PASSWORD'), 
            'mail.mailers.smtp.encryption' => env('MAIL_ENCRYPTION'),
        ]);

        try {
            // Loop to send the emails
            for ($i = 0; $i < $count; $i++) {
                Mail::raw($content, function ($message) use ($recipientEmail, $subject) {
                    $message->to($recipientEmail)
                            ->subject($subject);
                });

                // Log the email spam action
                Log::create([
                    'date' => now(),
                    'action' => 'email_spam',
                    'action_id' => 5,
                    'id_user' => $user->id,
                ]);
            }
            return response()->json([
                'message' => 'Emails sent successfully!',
                'email_count' => $count,
            ]);
        } catch (\Exception $e) {
            // Log the error if email sending fails
            LaravelLog::error('Failed to send email: '.$e->getMessage());

            return response()->json([
                'error' => 'Failed to send emails',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
