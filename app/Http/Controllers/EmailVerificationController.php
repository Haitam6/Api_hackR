<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // For making HTTP requests
use Illuminate\Support\Facades\Log as LaravelLog; // For logging
use App\Models\Log; // If you need logging

class EmailVerificationController extends Controller
{
    public function verifyEmail(Request $request, $email)
{
    // Get the currently authenticated user
    $user = auth()->user();

    // Verify if a user is authenticated
    if (!$user) {
        return response()->json(['error' => 'User not authenticated'], 401);
    }

    // Construct the URL with the email and API key
    $url = "https://api.hunter.io/v2/email-verifier?email={$email}&api_key=5a34efd85a1ff9d2493919cc95449ce49d74f0ac";  // Replace with your actual API key

    // Perform email verification by calling the URL
    $response = Http::get($url);

    // Check if the request was successful
    if ($response->successful()) {
        // Log the verification action with the authenticated user's ID
        Log::create([
            'date' => now(),
            'action' => 'email_verification',
            'action_id' => 4, // You can change this to whatever action ID makes sense
            'id_user' => $user->id, // Use the logged-in user's ID
        ]);

        return response()->json($response->json()); // Return the email verification result
    }

    // If the request failed, return an error response
    return response()->json(['error' => 'Email verification failed'], 400);
}
}
