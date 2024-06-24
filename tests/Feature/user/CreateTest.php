<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;


// CREATE
it('allows create user', function () {
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

  // authorization token in headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];

  // New user data
  $newUser = [
    'name' =>'jose',
    'email' => 'ab@gmail.com',
    'password' => bcrypt($fake_password),
    'rut' => '11111111-1',
    'birthday'=> '1995/01/02',
    'address'=> 'valparaiso',
    'role_id' => 1,
  ];

  // POST request to create a new user
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


// You do not have permission to create a user
it('without permissions to create user', function () {
  $fake_email = 'newuser@example.com';
  $fake_password = 'Password123!';
  DB::beginTransaction();

  // Create a user to log in with role 2 of a normal non-admin user
  $userLogin = User::factory()->create([
    'name' =>'jose',
    'email' => $fake_email,
    'password' => bcrypt($fake_password),
    'rut' => '11111111-1',
    'birthday'=> '1995/01/02',
    'address'=> 'valparaiso',
    'role_id' => 2,
  ]);

  // Generate JWT token for that user
  $token = JWTAuth::fromUser($userLogin);

  // authorization token in headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];

  // New user data
  $newUser = [
    'name' =>'jose',
    'email' => 'ab@gmail.com',
    'password' => bcrypt($fake_password),
    'rut' => '11111111-1',
    'birthday'=> '1995/01/02',
    'address'=> 'valparaiso',
    'role_id' => 1,
  ];

  // POST request to create a new user
  $response = $this->withHeaders($headers)->postJson('/api/user/create', $newUser);

  $response->assertStatus(403);

  $response->assertHeader('Content-Type', 'application/json');

  $response->assertJson(['error' => 'No tienes permisos para realizar esta acción.']);

  DB::rollBack();
});


// user registration fails with invalid input
it('fails create user with invalid input', function () {
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

  // authorization token in headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];


  // New user data
  $newUser = [
   'name' =>'jose',
    'email' => 'invalid email',
    'password' => '1234',
    'rut' => '11111111-1',
    'birthday'=> '1995/01/02',
    'address'=> 'valparaiso',
    'role_id' => 1,
  ];

  // POST request to create a new user
  $response = $this->withHeaders($headers)->postJson('/api/user/create', $newUser);


  $response->assertStatus(422);
  $response->assertHeader('Content-Type', 'application/json');


  // check if the email json structure contains the 'error' field and if it has the expected format to show the validation error
  $response->assertJsonStructure([
    'error' => ['email']
  ]);

  // check if the password json structure contains the 'error' field and if it is in the expected format to display the validation error
  $response->assertJsonStructure([
    'error' => ['password']
  ]);

  // check if name has validation errors
  $response->assertJsonMissingValidationErrors(['name']);

  DB::rollBack();
});
