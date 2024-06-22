<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;


// CREATE
it('allows create user', function () {
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

  // token de autorización en los headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];

  // Datos del nuevo usuario
  $newUser = [
    'name' => 'joe',
    'email' => 'de@gmail.com',
    'password' => 'Password456!',
    'role_id' => 1
  ];

  // Solicitud POST para crear un nuevo usuario
  $response = $this->withHeaders($headers)->postJson('/api/user/create', $newUser);
  $response->assertStatus(201);
  $response->assertHeader('Content-Type', 'application/json');
  $response->assertJsonStructure([
    'status',
    'message',
    'user' => [
      'name',
      'email',
      'role_id'
    ],
  ]);

  DB::rollBack();
});


// No tiene permiso para crear un usuario

it('without permissions to create user', function () {
  DB::beginTransaction();

  // Crear un usuario para iniciar sesión con el rol 2 de un usuario normal no admin
  $userLogin = User::factory()->create([
    'name' => 'alberto',
    'email' => 'alberto@example.com',
    'password' => bcrypt('Password123!'),
    'role_id' => 2
  ]);

  // Generar JWT token para ese usuario 
  $token = JWTAuth::fromUser($userLogin);

  // token de autorización en los headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];

  // Datos del nuevo usuario
  $newUser = [
    'name' => 'joe',
    'email' => 'de@gmail.com',
    'password' => 'Password456!',
    'role_id' => 2
  ];

  // Solicitud POST para crear un nuevo usuario
  $response = $this->withHeaders($headers)->postJson('/api/user/create', $newUser);

  $response->assertStatus(403);

  $response->assertHeader('Content-Type', 'application/json');

  $response->assertJson(['error' => 'No tienes permisos para realizar esta acción.']);

  DB::rollBack();
});


// falla registro de usuario con un input no valido
it('fails create user with invalid input', function () {
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

  // token de autorización en los headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];


  // Datos del nuevo usuario
  $newUser = [
    'name' => 'joe',
    'email' => 'invalid-email',
    'password' => '1234',
    'role_id' => 2
  ];

  // Solicitud POST para crear un nuevo usuario
  $response = $this->withHeaders($headers)->postJson('/api/user/create', $newUser);


  $response->assertStatus(422);
  $response->assertHeader('Content-Type', 'application/json');


  // compruebo si la estructura del json de email contiene el campo 'error' y si tiene el formato esperado para mostrar el error de validacion
  $response->assertJsonStructure([
    'error' => ['email']
  ]);

  // compruebo si la estructura del json de password contiene el campo 'error' y si tiene el formato esperado para mostrar el error de validacion
  $response->assertJsonStructure([
    'error' => ['password']
  ]);

  // compruebo si name tiene errores de validacion 
  $response->assertJsonMissingValidationErrors(['name']);

  DB::rollBack();
});
