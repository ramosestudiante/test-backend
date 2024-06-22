<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;


// UPDATE

it('fails to update user with invalid input', function() {
    DB::beginTransaction();
    
    // Crear un usuario para iniciar sesión
    $userLogin = User::factory()->create([
        'id' => 40,
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
    
    // Datos del nuevo usuario a crear con datos inválidos
    $newUser = [
        'name' => 'joe', // Nombre válido
        'email' => 'invalid email', // Email inválido
        'password' => 'short', // Contraseña demasiado corta
        'role_id' => 1
    ];
    
    // Enviar solicitud PUT para modificar al usuario
    $response = $this->withHeaders($headers)->putJson("/api/user/{$userLogin->id}", $newUser);

    // Verificar que la solicitud devuelva un código de estado 422 (Unprocessable Entity)
    $response->assertStatus(422);

    // Verificar que la respuesta tiene el header 'Content-Type' como 'application/json'
    $response->assertHeader('Content-Type', 'application/json');

    // Verificar la estructura del JSON para errores de validación
    $response->assertJsonStructure([
        'errors' => [
            'email',
            'password',
        ]
    ]);

    $response->assertJsonMissingValidationErrors(['name']);

    DB::rollBack();
});

it('allows update user', function () {
    DB::beginTransaction();
    
    // Crear un usuario para iniciar sesión
    $userLogin = User::factory()->create([
        'id' => 40,
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
    
    // Datos del nuevo usuario a crear
    $newUser = [
        'name' => 'joe',
        'email' => 'albert@gmail.com',
        'password' => 'Password456!',
        'role_id' => 1
    ];
    
    // Enviar solicitud PUT para modificar al usuario
    $response = $this->withHeaders($headers)->putJson("/api/user/{$userLogin->id}", $newUser);

    $response->assertStatus(200);

    // Verificar que la respuesta tenga el header 'Content-Type' como 'application/json'
    $response->assertHeader('Content-Type', 'application/json');
    
    // Verificar que la estructura JSON devuelta sea la esperada
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
