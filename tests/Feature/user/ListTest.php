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
    'name' => 'jose',
    'email' => $fake_email,
    'password' => bcrypt($fake_password),
    'rut' => '11111111-1',
    'birthday' => '1995/01/02',
    'address' => 'valparaiso',
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

  // List of users to create
  $list = [
    [
      'name' => 'jose',
      'email' => 'jose@example.com',
      'rut' => '22222222-2',
      'birthday' => '1995/01/01',
      'address' => 'valparaiso',
      'password' => bcrypt('Password789!'),
      'role_id' => 1
    ],
    [
      'name' => 'maria',
      'email' => 'maria@example.com',
      'rut' => '33333333-3',
      'birthday' => '1995/01/01',
      'address' => 'valparaiso',
      'password' => bcrypt('Password456!'),
      'role_id' => 2
    ]
  ];

  // Create users
  foreach ($list as $userData) {
    User::create($userData);
  }

  $response = $this->withHeaders($headers)->getJson('/api/users');

  $response->assertStatus(200);
  $response->assertHeader('Content-Type', 'application/json');

  $response->assertJsonStructure([
    'status',
    'users' => [
      'current_page',
      'data' => [
        '*' => [
          'id',
          'role_id',
          'name',
          'email',
          'rut',
          'birthday',
          'address',
          'email_verified_at',
          'created_at',
          'updated_at',
        ],
      ],
      'first_page_url',
      'from',
      'last_page',
      'last_page_url',
      'links' => [
        '*' => [
          'url',
          'label',
          'active',
        ],
      ],
      'next_page_url',
      'path',
      'per_page',
      'prev_page_url',
      'to',
      'total',
    ],
  ]);

  DB::rollBack();
});


// Test that accessing the user list without authentication fails
it('fails to show user list without authentication', function () {
  $response = $this->getJson('/api/users');

  // response status is 401 Unauthorized
  $response->assertStatus(401);
  $response->assertHeader('Content-Type', 'application/json');
  $response->assertJsonStructure([
    'message',
  ]);
});
