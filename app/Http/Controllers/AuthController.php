<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
  public function __construct()
  {
    //  tanto login como register tendran una excepcion del middleware de auth no requeriran autenticacion

    $this->middleware('auth:api', ['except' => ['login', 'register']]);
  }

  public function login(Request $request)
  {
    try {
      // Valido los campos de email y password
      $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:8',
      ]);

      // Obtengo solo los datos emall y password de la request
      $credentials = $request->only('email', 'password');

      // verifico si el campo remember en el request existe
      $remember = $request->filled('remember');

      // Verifico si no existe el token oh si las credenciales y el remember no coinciden las credenciales son invalidas
      if (!$token = JWTAuth::attempt($credentials, $remember)) {
        return response()->json(['error' => 'Credenciales inválidas'], 401);
      }

      $user = Auth::user();

      // Genero una cookie de autorización
      // factory crea el token de jwtAuth
      // getttl time to live  tiempo de vida del token
      $cookie = Cookie::make('jwt_token', $token, JWTAuth::factory()->getTTL() * 60, '/', null, false, true); // Secure=true; HttpOnly=true

      return response()->json([
        'status' => 'success',
        'user' => $user,
      ], 200)->withCookie($cookie);
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

      // Valido los campos de name,email y password con sus reglas de validacion

      $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => [
          'required',
          'string',
          Password::min(8) // Mínimo 8 caracteres de longitud
            ->mixedCase() // Debe contener letras mayúsculas y minúsculas
            ->numbers() // Debe contener al menos un número
            ->symbols(), // Debe contener al menos un símbolo
        ],
      ],  [
        'name.required' => 'El nombre es obligatorio.',
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
        'email.unique' => 'El correo electrónico ya está registrado.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.password' => 'La contraseña debe incluir al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
      ]);

      // Crear un nuevo usuario y dejo por defecto el rol 2 que es un usario normal no admins
      $user = new User();
      $user->name = $request->input('name');
      $user->email = $request->input('email');
      $user->password = Hash::make($request->input('password'));
      $user->role_id = $request->input('role_id', 2);
      $user->save();

      // Autenticar al usuario después del registro
      Auth::login($user);

      // Generación del token JWT opcional para devolverlo en la respuesta
      $token = JWTAuth::fromUser($user);

      // Devolver una respuesta de éxito
      return response()->json([
        'status' => 'success',
        'message' => 'Usuario registrado exitosamente',
        'user' => $user,
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
