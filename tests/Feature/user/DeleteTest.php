<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;


// DELETE
// Delete a user
it('allows delete user', function () {
  $fake_email = 'newuser@example.com';
  $fake_password = 'Password123!';
  DB::beginTransaction();

  // Create a user to log in
  $userLogin = User::factory()->create([
    'id' =>20,
    'name' =>'jose',
    'email' => $fake_email,
    'password' => bcrypt($fake_password),
    'rut' => '11111111-1',
    'birthday'=> '1995/01/02',
    'address'=> 'valparaiso',
    'role_id' => 1,
  ]);

  // Generate JWT token for that user
  $token = JWTAuth::fromUser($userLogin);

  // Authorization token in headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];
  // POST request to create a new user
  $response = $this->withHeaders($headers)->deleteJson('/api/users/20',);


  $response->assertStatus(200);
  $response->assertHeader('Content-Type', 'application/json');
  $response->assertJsonStructure(['status', 'user']);

  DB::rollBack();
});


// Error deleting a user that does not exist
it('fails id to delete user', function () {
  $fake_email = 'newuser@example.com';
  $fake_password = 'Password123!';
  DB::beginTransaction();

  // Create a user to log in
  $userLogin = User::factory()->create([
    'name' =>'jose',
    'email' => $fake_email,
    'password' => bcrypt($fake_password),
    'rut' => '11111111-1',
    'birthday'=> '1995/01/02',
    'address'=> 'valparaiso',
    'role_id' => 1,
  ]);

  // Generate JWT token for that user
  $token = JWTAuth::fromUser($userLogin);

  // Authorization token in headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];

  $nonExistentId = 9999;

  // Delete request to delete a user that does not exist
  $response = $this->withHeaders($headers)->deleteJson("/api/users/{$nonExistentId}");


  $response->assertStatus(404);
  $response->assertHeader('Content-Type', 'application/json');
  $response->assertJson([
    'error' => 'Usuario no encontrado'
  ]);

  DB::rollBack();
});
