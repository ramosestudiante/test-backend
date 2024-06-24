<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

//LIST

// Show user list
it('allows user to show list', function () {
  $fake_email = 'newuser@example.com';
  $fake_password = 'Password123!';
  DB::beginTransaction();
  // Create a user
  $users = User::factory()->create([
    'name' =>'jose',
    'email' => $fake_email,
    'password' => bcrypt($fake_password),
    'rut' => '11111111-1',
    'birthday'=> '1995/01/02',
    'address'=> 'valparaiso',
    'role_id' => 1,
  ]);

  // Generate JWT token for that user 
  $user = $users->first();
  $token = JWTAuth::fromUser($user);

  // Authorization token in headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];


  // once I have the user's token I can see the list

  // list of users
  $list = [
    [
      'name' => 'jose',
      'email' => 'jose@example.com',
      'rut'=> '22222222-2',
      'birthday' => '1995/01/01',
      'address'=> 'valparaiso',
      'password' => bcrypt('Password789!'),
      'role_id' => 1
    ],
    [
      'name' => 'maria',
      'email' => 'maria@example.com',
      'rut'=> '33333333-3',
      'birthday' => '1995/01/01',
      'address'=> 'valparaiso',
      'password' => bcrypt('Password456!'),
      'role_id' => 2
    ]
  ];

  // according to the list I create 2 users and then show it in the api
  foreach ($list as $userData) {
    User::create([
      'name' => $userData['name'],
      'email' => $userData['email'],
      'rut' => $userData['rut'],
      'birthday' => $userData['birthday'],
      'address' => $userData['address'],
      'password' => $userData['password'],
      'role_id' => $userData['role_id']
    ]);
  }
  // I pass the token to the bearer through the headers to the route
  $response = $this->withHeaders($headers)->getJson('/api/user/list');

  $response->assertStatus(200);
  $response->assertHeader('Content-Type', 'application/json');

  // I pass only the non-sensitive data that does not compromise the security of; user, for example do not give him a password
  $response->assertJsonStructure([
    'status',
    'users' => ['*' => [
      'name',
      'email',
      'rut',
      'birthday',
      'address',
      'role_id',
    ]],
  ]);
  DB::rollBack();
});
