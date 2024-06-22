<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;


// DELETE

it('allows delete user', function () {
  DB::beginTransaction();

  // Crear un usuario para iniciar sesión
  $userLogin = User::factory()->create([
    'Id' => 20,
    'name' => 'alberto',
    'email' => 'alberto@example.com',
    'password' => bcrypt('Password123!'),
    'role_id' => 1
  ]);

  // Generar JWT token para ese usuario 
  $token = JWTAuth::fromUser($userLogin);

  // Token de autorización en los headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];
  // Solicitud POST para crear un nuevo usuario
  $response = $this->withHeaders($headers)->deleteJson('/api/user/20',);


  $response->assertStatus(200);
  $response->assertHeader('Content-Type', 'application/json');
  $response->assertJsonStructure(['status', 'user']);

  DB::rollBack();
});


// Error en eliminar un usuario que no existe
it('fails id to delete user', function () {
  DB::beginTransaction();

  // Crear un usuario para iniciar sesión
  $userLogin = User::factory()->create([
    'name' => 'alberto',
    'email' => 'alberto@example.com',
    'password' => bcrypt('Password123!'),
    'role_id' => 1
  ]);

  // Generar JWT token para ese usuario 
  $token = JWTAuth::fromUser($userLogin);

  // Token de autorización en los headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];

  $nonExistentId = 9999;

  // Solicitud DELETE para eliminar un usuario que no existe
  $response = $this->withHeaders($headers)->deleteJson("/api/user/{$nonExistentId}");


  $response->assertStatus(404);
  $response->assertHeader('Content-Type', 'application/json');
  $response->assertJson([
    'error' => 'Usuario no encontrado'
  ]);

  DB::rollBack();
});
