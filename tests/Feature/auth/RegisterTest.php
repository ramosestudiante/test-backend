<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;

// Prueba de registro de un usuario crea un usuario y una vez creado hace el rollback que elimina el usuario
it('allows user to register', function () {
  $fake_email = 'newuser@example.com';
  $fake_password = 'Password123!';

  DB::beginTransaction();

  $response = $this->postJson('/api/register', [
    'name' => 'New User',
    'role_id' => 2,
    'email' => $fake_email,
    'password' => bcrypt($fake_password),
  ]);

  $response->assertStatus(201);
  $response->assertHeader('Content-Type', 'application/json');
  $response->assertJsonStructure([
    'status',
    'message',
    'user' => ['id', 'name', 'email', 'role_id', 'created_at', 'updated_at'],
  ]);

  DB::rollBack();
});

// falla registro de usuario con un input no valido
it('fails registration with invalid input', function () {
  DB::beginTransaction();
  $response = $this->postJson('/api/register', [
    'name' => 'jhon',
    'role_id' => 2,
    'email' => 'invalid-email',
    'password' => '1234',
  ]);

  $response->assertStatus(422);
  $response->assertHeader('Content-Type', 'application/json');

  // compruebo si name si tiene errores de validacion 
  $response->assertJsonMissingValidationErrors(['name']);

  // compruebo si la estructura del json de email contiene el campo 'error' y si tiene el formato esperado sale el error de validacion
  $response->assertJsonStructure([
    'error' => ['email']
  ]);

  // compruebo si la estructura del json de password contiene el campo 'error' y si tiene el formato esperado sale el error de validacion
  $response->assertJsonStructure([
    'error' => ['password']
  ]);
  DB::rollBack();
});


// verifico si el email esta duplicado
it('fails registration with duplicate email', function () {
  DB::beginTransaction();
  // creo un usuario
  $existingUser = User::factory()->create([
    'name' => 'Test User',
    'email' => 'duplicate@example.com',
    'role_id' => 2,
    'password' => 'ValidPassword123!',
  ]);

  // ingreso el usuario con el mismo correo
  $response = $this->postJson('/api/register', [
    'name' => 'Test User',
    'email' => 'duplicate@example.com',
    'role_id' => 2,
    'password' => 'ValidPassword123!',
  ]);

  $response->assertStatus(422);
  $response->assertHeader('Content-Type', 'application/json');

  // compruebo si la estructura del json de email contiene el campo 'error' y si tiene el formato esperado sale o se muestra el error de validacion
  $response->assertJsonStructure([
    'error' => ['email']
  ]);
  DB::rollBack();
});
