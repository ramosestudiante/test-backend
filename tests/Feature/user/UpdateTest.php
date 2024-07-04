<?php

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;


// UPDATE

// fails to update a user with invalid inputs
it('fails to update user with invalid input', function() {
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
   
    // Configure the authorization token in the headers
    $headers = [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ];
    
    // Data of the new user to be created with invalid data
    $newUser = [
        'name' =>'jose',
        'email' => 'invalid email',
        'birthday'=> '1995/01/02',
        'address'=> 'valparaiso',
    ];
    
    // Send PUT request to modify the user
    $response = $this->withHeaders($headers)->patchJson("/api/users/{$userLogin->id}", $newUser);

    $response->assertStatus(422);
    $response->assertHeader('Content-Type', 'application/json');

    // Check the JSON structure for validation errors
    $response->assertJsonStructure([
        'errors' => [
            'email']
    ]);

    $response->assertJsonMissingValidationErrors(['name']);

    DB::rollBack();
});

// Update user
it('allows update user', function () {
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
   
    // Configure the authorization token in the headers
    $headers = [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ];
    
    // Data of the new user to create
    $newUser = [
        'name' =>'jose',
        'email' => 'albes@gmail.com',
        'birthday'=> '1995/01/02',
        'address'=> 'valparaiso',
    ];
    
    // Send PUT request to modify the user
    $response = $this->withHeaders($headers)->patchJson("/api/users/{$userLogin->id}", $newUser);

    $response->assertStatus(200);

    $response->assertHeader('Content-Type', 'application/json');
    
    // Verify that the JSON structure returned is as expected
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
