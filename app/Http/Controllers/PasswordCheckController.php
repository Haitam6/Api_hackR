<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // To access stored files

class PasswordCheckController extends Controller
{
    public function isCommonPassword(Request $request)
    {
        // Get the password from the request
        $password = $request->input('password');
        
        // Ensure the file path is correct
        $filePath = storage_path('common-passwords/10k-most-common.txt');

        // Check if file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Password list file not found'], 500);
        }

        // Read file contents into an array
        $commonPasswords = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Check if the password exists in the array
        if (in_array($password, $commonPasswords)) {
            return response()->json(['message' => 'The password is common'], 200);
        } else {
            return response()->json(['message' => 'The password is not common'], 200);
        }
    }
}
