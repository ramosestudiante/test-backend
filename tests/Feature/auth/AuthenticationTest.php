<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;

// Prueba de inicio de sesion de un usuario
// creo al usuario y ocupo transaccion para esperar que se cree el usuario y haga la prueba de login y una vez listo hace el rollback

it('allows user to login', function () {
  $fake_email = 'fake@example.com';
  $fake_password = 'password';

  DB::beginTransaction();
  $user = User::factory()->create([
    'email' => $fake_email,
    'password' => bcrypt($fake_password),
    'role_id' => 1,
  ]);

  $response = $this->postJson('/api/login', [
    'email' => $fake_email,
    'password' => $fake_password,
  ]);

  $response->assertOk(201);
  $response->assertHeader('Content-Type', 'application/json');
  $response->assertJsonStructure(['status', 'user']);
  DB::rollBack();
});

// falla inicio de sesion con un input no valido sea email o password
it('fails login with invalid input', function () {
  $response = $this->postJson('/api/login', [
    'email' => 'invalid_email',
    'password' => 'short',
  ]);

  $response->assertStatus(422);
  $response->assertHeader('Content-Type', 'application/json');

  // Verifica que haya errores de validación para el campo email o password 
  $response->assertJsonValidationErrors(['email', 'password']);
});


it('fails login with invalid credentials', function () {
  $response = $this->postJson('/api/login', [
    'email' => 'this_email_does_not_exists@example.com',
    'password' => 'this_password_a_wrong',
  ]);
  $response->assertStatus(401);
  $response->assertHeader('Content-Type', 'application/json');
  $response->assertJson(['error' => 'Credenciales inválidas']);
});
