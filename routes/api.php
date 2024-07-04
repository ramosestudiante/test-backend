<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
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
// Redirigir la ruta raíz a la documentación de Swagger



Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/refresh', [AuthController::class, 'refresh']);


Route::middleware('auth:api')->get('/validatetoken', [AuthController::class, 'validationToken']);

// Protected routes (require authentication and admin to be able to create, delete and update
Route::middleware(['auth:api', 'admin'])->prefix('users')->group(function () {
  Route::post('/', [UserController::class, 'create']);
  Route::delete('/{id}', [UserController::class, 'delete']);
  Route::patch('/{id}', [UserController::class, 'update']);
});

// only require user authentication to be able to display the list of users and see the user by id...
Route::middleware('auth:api')->prefix('users')->group(function () {
  Route::get('/', [UserController::class, 'list']);
  Route::get('/{id}', [UserController::class, 'show']);
});

// /**
//  * Swagger
//  */
Route::get('/api.yaml', function () {
  $path = resource_path('docs/swagger/api.yaml');

  if (!File::exists($path)) {
      abort(404);
  }

  $content = File::get($path);
  return Response::make($content, 200, [
      'Content-Type' => 'application/yaml',
      'Content-Disposition' => 'inline; filename="api.yaml"'
  ]);
});

Route::get('/docs', function () {
  return view('swagger');
});


// Route::get('/login', function () {
//     return response()->json(['error' => 'Unauthorized'], 401);
// })->name('login');
