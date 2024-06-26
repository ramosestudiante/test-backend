<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;

// Test user login
// I create the user and occupy transaction to wait for the user to be created and perform the login test and once ready, perform the rollback
it('allows user to login', function () {
  $fake_email = 'fake@example.com';
  $fake_password = 'Password123!';

  DB::beginTransaction();
  User::factory()->create([
    'email' => $fake_email,
    'password' => bcrypt($fake_password),
    'rut' => '11111111-1',
    'birthday'=> '1995/01/02',
    'address'=> 'valparaiso',
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

// login fails with an invalid input, be it email or password
it('fails login with invalid input', function () {
  $response = $this->postJson('/api/login', [
    'email' => 'invalid_email',
    'password' => 'short',
  ]);

  $response->assertStatus(422);
  $response->assertHeader('Content-Type', 'application/json');

  // Verify that there are validation errors for the email or password field
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
