<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ValidToken;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post("/register", [AuthController::class, "register"])->name("register");
Route::post("/login", [AuthController::class, "login"])->name("login");
Route::post("/login", [AuthController::class, "login"])->name("login");
Route::post("/forgot-password", [AuthController::class, "forgotPassword"])->name("forgotPassword");
Route::post('/reset-password', [AuthController::class, 'resetPasswordByToken'])->name("password.reset");

Route::middleware([ValidToken::class])->group(function (){
   Route::post("/logout", [AuthController::class, "logout"])->name("logout");
});
