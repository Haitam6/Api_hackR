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

// Voici les routes post de l'application

Route::post('/auth/register', [AuthController::class, 'register']);

Route::post('/auth/login', [AuthController::class, 'login']);

Route::put('/roles/assign', [RolesController::class, 'giveRules']);

Route::post('/passwords/check', [PasswordCheckController::class, 'isCommonPassword']);

Route::post('/emails/spam-actions', [EmailSpammerController::class, 'spamEmails']); 


Route::post('/tests/ddos', [DdosController::class, 'DdosTest']);

Route::post('/phishing', [PhishController::class, 'handlePhish']);

Route::post('/crawlers', [CrawlerController::class, 'crawlerInformation']);

// Voici les routes get de l'application

Route::get('/users/me', [AuthController::class, 'me']); 

Route::get('/emails/verify/{email}', [EmailVerificationController::class, 'verifyEmail']);

Route::get('/domains/{domain}/subdomains', [DomainController::class, 'getSubdomains']);

Route::get('/identities/fake', [FakeIdentityController::class, 'generateFakeIdentity']);

Route::get('/images/random', [ImageController::class, 'fetchRandomImage']);

Route::get('/passwords/generate', [PasswordGeneratorController::class, 'generateSecurePassword']);

Route::get('/logs/functionality/{nom_fonctionnalite}', [LogController::class, 'getLogsByFunctionality']);

Route::get('/logs/recent', [LogController::class, 'getLastLogs']);

Route::get('/logs/user', [LogController::class, 'getLogsByUser']);