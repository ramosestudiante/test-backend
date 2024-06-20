<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
// Route::get('/list', [UserController::class, 'list']);


Route::middleware('auth.api')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth:api','admin'])->prefix('user')->group(function () {
    Route::get('/list', [UserController::class, 'list']);
    Route::post('/create', [UserController::class, 'create']);
    Route::delete('/{id}', [UserController::class, 'delete']);
    Route::put('/{id}',[UserController::class,'update']);
    
});

Route::get('/login', function () {
    return response()->json(['error' => 'Unauthorized'], 401);
})->name('login');

