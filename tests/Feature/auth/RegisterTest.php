<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;

// User registration test creates a user and once created does a rollback that deletes the user
it('allows user to register', function () {
  $fake_email = 'newuser@example.com';
  $fake_password = 'Password123!';

  DB::beginTransaction();

  $response = $this->postJson('/api/register', [
    'name' =>'jose',
    'email' => $fake_email,
    'password' => bcrypt($fake_password),
    'rut' => '11111111-1',
    'birthday'=> '1995/01/02',
    'address'=> 'valparaiso',
    'role_id' => 1,
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

// user registration fails with invalid input
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

  // check if name has validation errors
  $response->assertJsonMissingValidationErrors(['name']);

  // I check if the email json structure contains the 'error' field and if it has the expected format, the validation error appears
  $response->assertJsonStructure([
    'error' => ['email']
  ]);

  // I check if the password json structure contains the 'error' field and if it has the expected format, the validation error appears
  $response->assertJsonStructure([
    'error' => ['password']
  ]);
  DB::rollBack();
});


// check if the email is duplicate
it('fails registration with duplicate email', function () {
  $fake_email = 'newuser@example.com';
  $fake_password = 'Password123!';
  DB::beginTransaction();
  // create a user
  User::factory()->create([
    'name' =>'jose',
    'email' => $fake_email,
    'password' => bcrypt($fake_password),
    'rut' => '11111111-1',
    'birthday'=> '1995/01/02',
    'address'=> 'valparaiso',
    'role_id' => 1,
  ]);

  // enter the user with the same email
  $response = $this->postJson('/api/register', [
    'name' => 'Test User',
    'email' => 'newuser@example.com',
    'role_id' => 2,
    'password' => 'ValidPassword123!',
  ]);

  $response->assertStatus(422);
  $response->assertHeader('Content-Type', 'application/json');

  // I check if the email json structure contains the 'error' field and if it has the expected format, the validation error is displayed
  $response->assertJsonStructure([
    'error' => ['email']
  ]);
  DB::rollBack();
});
