<?php

namespace App\Http\Controllers;

use App\Helpers\ValidationRules;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
  public function __construct()
  {
    // both login and register will have an exception from the auth middleware, they will not require authentication

    $this->middleware('auth:api', ['except' => ['login', 'register']]);
  }

  public function login(Request $request)
  {
    try {
      // Validate email and password fields
      $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:8',
      ]);

      // Obtain only the email and password data from the request
      $credentials = $request->only('email', 'password');

      // check if the remember field in the request exists
      $remember = $request->filled('remember');

      

      // I check if the token does not exist oh if the credentials and Remember do not match the credentials are invalid
      if (!$token = JWTAuth::attempt($credentials, $remember)) {
        return response()->json(['error' => 'Credenciales inválidas'], 401);
      }

      $user = JWTAuth::user();

      // Generate an authorization cookie
      // factory creates the jwtAuth token
      // getTTL time to live token lifetime
      $cookie = Cookie::make('jwt_token', $token, JWTAuth::factory()->getTTL() * 60, '/', null, false, true); // Secure=true; HttpOnly=true

      $response = response()->json([
        'status' => 'success',
        'user' => $user,
        'token' => $token
    ], 200);
    
    $response->withCookie($cookie);
    
    return $response;
    } catch (ValidationException $e) {
      return response()->json(['errors' => $e->validator->errors()], 422);
    } catch (JWTException $e) {
      return response()->json(['error' => 'No se pudo crear el token'], 500);
    } catch (\Exception $e) {
      return response()->json(['error' => 'Error en el servidor. Por favor, inténtelo de nuevo más tarde.'], 500);
    }
  }


  public function register(Request $request)
  {
    try {

      // Validate the name, email and password fields with their validation rules
      $request->validate(
        ValidationRules::userRules(),
        ValidationRules::userMessages()
      );
      // Validate the RUT
      if (!validarRut($request->input('rut'))) {
        return response()->json(['error' => 'El RUT ingresado no es válido.'], 422);
      }

      // Create a new user and leave role 2 by default, which is a normal non-admin user
      $user = new User();
      $user->name = $request->input('name');
      $user->email = $request->input('email');
      $user->rut = $request->input('rut');
      $user->birthday = $request->input('birthday');
      $user->address = $request->input('address');
      $user->password = Hash::make($request->input('password'));
      $user->role_id = Role::USER;
      $user->save();


      // Generate a JWT token for the user
      $token = JWTAuth::fromUser($user);
      
      return response()->json([
        'status' => 'success',
        'message' => 'Usuario registrado exitosamente',
        'user' => $user,
        'token' => $token,
      ], 201);
    
    } catch (ValidationException $e) {
      return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Throwable $e) {
      if (strpos($e->getMessage(), 'SQLSTATE[23000]') !== false) {
        return response()->json(['error' => 'El correo electrónico ya está registrado.'], 422);
      }
      return response()->json(['error' => 'No se pudo registrar el usuario'], 500);
    }
  }
}
