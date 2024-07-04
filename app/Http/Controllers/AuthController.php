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
    $this->middleware('auth:api', ['except' => ['login', 'register']]);
  }

  public function login(Request $request)
  {
    try {
      // Validate email and password
      $request->validate([
        'email' => 'required|email',
        'password' => [
          'required',
          'string',
          \Illuminate\Validation\Rules\Password::min(8) // Mínimo 8 caracteres
        ],
      ], [
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
      ]);

      // Get email and password request
      $credentials = $request->only('email', 'password');

      // verify remember request
      $remember = $request->filled('remember');

      // token JWT
      if (!$token = JWTAuth::attempt($credentials, $remember)) {
        return response()->json(['error' => 'Credenciales inválidas'], 401);
      }

      // user authenticated
      $user = JWTAuth::user();

      // expiration token
      $refreshTokenTTL = now()->addWeeks(2); // time life refresh token
      // create refresh token
      $refreshToken = JWTAuth::claims(['exp' => $refreshTokenTTL])->fromUser($user);

      // get time life access_token
      $accessTokenTTL = JWTAuth::factory()->getTTL() * 60;
      $expiresAt = now()->addSeconds($accessTokenTTL);

      $response = [
        'status' => 'success',
        'user' => $user,
        'access_token' => $token,
        'refresh_token' => $refreshToken,
        'expires_at' => $expiresAt->toDateTimeString(),
        'refresh_expires_at' => $refreshTokenTTL->toDateTimeString(),
      ];
      // Establecer cookies para access_token y refresh_token
      $accessTokenTTL = JWTAuth::factory()->getTTL() * 60;

      return response()->json($response);
    } catch (ValidationException $e) {
      return response()->json(['errors' => $e->validator->errors()], 422);
    } catch (JWTException $e) {
      return response()->json(['error' => 'No se pudo crear el token', 'details' => $e->getMessage()], 500);
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
      $refreshToken = JWTAuth::claims(['type' => 'refresh'])->fromUser($user);

      $response =  response()->json([
        'status' => 'success',
        'message' => 'Usuario registrado exitosamente',
        'user' => $user,
        'access_token' => $token,
        'refresh_token' => $refreshToken
      ], 201);
      // add token in header response
      $response->header('Authorization', 'Bearer ' . $token);

      return $response;
    } catch (ValidationException $e) {
      return response()->json(['error' => $e->validator->errors()], 422);
    } catch (\Throwable $e) {
      if (strpos($e->getMessage(), 'SQLSTATE[23000]') !== false) {
        return response()->json(['error' => 'El correo electrónico ya está registrado.'], 422);
      }
      return response()->json(['error' => 'No se pudo registrar el usuario'], 500);
    }
  }

  public function refresh(Request $request)
  {
    try {
      // Get the current token from the request
      if (!$token = JWTAuth::getToken()) {
        return response()->json(['error' => 'No se encontró un token válido para refrescar'], 401);
      }

      // Attempt to refresh the token
      $newToken = JWTAuth::refresh($token);

      return response()->json([
        'status' => 'success',
        'access_token' => $newToken,
        'refresh_token' => $newToken,
      ])->header('Authorization', 'Bearer ' . $newToken);
    } catch (JWTException $e) {
      return response()->json(['error' => 'No se pudo refrescar el token', 'details' => $e->getMessage()], 500);
    }
  }

  public function validationToken(Request $request)
  {
    try {
      // GET Validate token JWT
      $user = JWTAuth::parseToken()->authenticate();

      return response()->json(['user' => $user], 200);

    } catch (\Exception $e) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }
}
