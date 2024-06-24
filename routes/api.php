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


Route::middleware('auth.api')->get('/user', function (Request $request) {
  return $request->user();
});

// Protected routes (require authentication and admin to be able to create, delete and update
Route::middleware(['auth:api', 'admin'])->prefix('user')->group(function () {
  Route::post('/create', [UserController::class, 'create']);
  Route::delete('/{id}', [UserController::class, 'delete']);
  Route::put('/{id}', [UserController::class, 'update']);
});

// only require user authentication to be able to display the list of users and see the user by id...
Route::middleware(['auth:api'])->prefix('user')->group(function () {
  Route::get('/list', [UserController::class, 'list']);
  Route::get('/{id}', [UserController::class, 'show']);
});


// Route::get('/login', function () {
//     return response()->json(['error' => 'Unauthorized'], 401);
// })->name('login');
