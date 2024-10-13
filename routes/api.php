<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordCheckController;
use App\Http\Controllers\EmailSpammerController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/me', [AuthController::class, 'me']); // Protected route to get current user info
Route::get('/verify-email/{email}', [EmailVerificationController::class, 'verifyEmail']);
Route::post('/check-password', [PasswordCheckController::class, 'isCommonPassword']);
Route::post('/spam-emails', [EmailSpammerController::class, 'spamEmails']);
