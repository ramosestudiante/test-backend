<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

it('fails show user', function () {
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
   
    // Configurar el token de autorización en los headers
    $headers = [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ];
    // Enviar solicitud DELETE para eliminar un usuario que no existe
    $nonExistentId = 9999;
    // Enviar solicitud POST para crear un nuevo usuario
   $response = $this->withHeaders($headers)->getJson("/api/user/{$nonExistentId}");

    
   $response->assertStatus(404);
   $response->assertHeader('Content-Type', 'application/json');
   $response->assertJson([
    'error' => 'Usuario no encontrado'
]);

   DB::rollBack();
});