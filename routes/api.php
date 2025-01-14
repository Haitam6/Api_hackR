<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordCheckController;
use App\Http\Controllers\EmailSpammerController;
use App\Http\Controllers\PasswordGeneratorController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\FakeIdentityController;
use App\Http\Controllers\DdosController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PhishController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\CrawlerController;
use App\Http\Controllers\LogController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/users/me', [AuthController::class, 'me']); 
Route::post('/giveRules', [RolesController::class, 'giveRules']);


Route::get('/verify-email/{email}', [EmailVerificationController::class, 'verifyEmail']);
Route::post('/check-password', [PasswordCheckController::class, 'isCommonPassword']);
Route::post('/emails/spam', [EmailSpammerController::class, 'spamEmails']); 
Route::get('/generate-password', [PasswordGeneratorController::class, 'generateSecurePassword']);
Route::get('/subdomains/{domain}', [DomainController::class, 'getSubdomains']);
Route::get('/generate-fake-identity', [FakeIdentityController::class, 'generateFakeIdentity']);
Route::post('/Ddos', [DdosController::class, 'DdosTest']);
Route::get('/random-image', [ImageController::class, 'fetchRandomImage']);
Route::post('/phish', [PhishController::class, 'handlePhish']);
Route::post('/phish/handlePhishData', [PhishController::class, 'handlePhishData']);
Route::post('/crawlerInformation', [CrawlerController::class, 'crawlerInformation']);
Route::post('/getLastLogs', [LogController::class, 'getLastLogs']);
Route::post('/getLogsByUser', [LogController::class, 'getLogsByUser']);
Route::post('/getLogsByFunctionality', [LogController::class, 'getLogsByFunctionality']);
