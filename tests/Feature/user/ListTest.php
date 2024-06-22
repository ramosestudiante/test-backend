<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

//LIST

// Mostrar lista de usuario
it('allows user to show list', function () {
  DB::beginTransaction();
  // Crear un usuario
  $users = User::factory()->create([
    'name' => 'alberto',
    'email' => 'alberto@example.com',
    'password' => bcrypt('Password123!'),
    'role_id' => 1
  ]);

  // Generar JWT token para ese usuario 
  $user = $users->first();
  $token = JWTAuth::fromUser($user);

  // Token de autorización en los headers
  $headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
  ];


  // una vez que tengo el token del usuario podre ver la lista

  // lista de usuarios
  $list = [
    [
      'name' => 'jose',
      'email' => 'jose@example.com',
      'password' => bcrypt('Password789!'),
      'role_id' => 1
    ],
    [
      'name' => 'maria',
      'email' => 'maria@example.com',
      'password' => bcrypt('Password456!'),
      'role_id' => 2
    ]
  ];

  // de acuerdo a la lista creo a 2 usuarios para luego mostrarlo en la api
  foreach ($list as $userData) {
    User::create([
      'name' => $userData['name'],
      'email' => $userData['email'],
      'password' => $userData['password'],
      'role_id' => $userData['role_id']
    ]);
  }
  // le paso el bearear token por el headers a la ruta
  $response = $this->withHeaders($headers)->getJson('/api/user/list');

  $response->assertStatus(200);
  $response->assertHeader('Content-Type', 'application/json');

  // le paso solo los datos no sensibles que no compromete la seguridad de; usuario, por ejemplo no le pase password
  $response->assertJsonStructure([
    'status',
    'users' => ['*' => [
      'name',
      'email',
      'role_id',
    ]],
  ]);
  DB::rollBack();
});
