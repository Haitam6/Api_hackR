<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordCheckController;
use App\Http\Controllers\EmailSpammerController;
use App\Http\Controllers\PasswordGeneratorController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/users/me', [AuthController::class, 'me']); // Protected route to get current user info
Route::get('/verify-email/{email}', [EmailVerificationController::class, 'verifyEmail']);
Route::post('/check-password', [PasswordCheckController::class, 'isCommonPassword']);
Route::post('/emails/spam', [EmailSpammerController::class, 'spamEmails']); // Updated for resource naming
Route::post('/generate-password', [PasswordGeneratorController::class, 'generateSecurePassword']);
