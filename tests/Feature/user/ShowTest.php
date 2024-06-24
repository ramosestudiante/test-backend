<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

it('fails show user', function () {
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
   
    // Set authorization token in headers
    $headers = [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ];
    // Send DELETE request to delete a user that does not exist
    $nonExistentId = 9999;
   // Send POST request to create a new user
   $response = $this->withHeaders($headers)->getJson("/api/user/{$nonExistentId}");

    
   $response->assertStatus(404);
   $response->assertHeader('Content-Type', 'application/json');
   $response->assertJson([
    'error' => 'Usuario no encontrado'
]);

   DB::rollBack();
});