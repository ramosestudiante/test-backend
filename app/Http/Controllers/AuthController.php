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
        // hago enfoque a que login y register no tendran el middleware de auth por eso hago una expcion a logun y register

        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        try {
            // valido los campos de email y password 
            $request->validate([
                'email' => 'required|string|email',
                'password' => [
                    'required',
                    'string',
                    // Password::min(8) Mínimo 8 caracteres de longitud
                    // ->mixedCase() // Debe contener letras mayúsculas y minúsculas
                    // ->numbers() // Debe contener al menos un número
                    // ->symbols(), // Debe contener al menos un símbolo
                ],
            ],  [
                'password.confirmed' => 'Las contraseñas no coinciden.',
                // 'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                // 'password.password' => 'La contraseña debe incluir al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
            ]);

            // obtengo solo los datos emall y password de la request
            $credentials = $request->only('email', 'password');
            $remember = $request->filled('remember');

            // aqui attempt devuelve el token de jwt valido si las credenciales del request son correctas
            $token = JWTAuth::attempt($credentials, $remember);

            // entonces si no existe el token me retorna credenciales invalidasr
            if (!$token) {
                return response()->json(['error' => 'Credenciales inválidas'], 401);
            }

            $user = Auth::user();
            
            // Generar una cookie de autorización
            $cookie = Cookie::make('jwt_token', $token, JWTAuth::factory()->getTTL() * 60, '/', null, false, true); // Secure=true; HttpOnly=true


            // factory genera el token de jwtAuth
            // getttl time to live  tiempo de vida del token
            return response()->json([
                'status' => 'success',
                'user' => $user,
                // 'authorization' => [
                //     'token' => $token,
                //     'type' => 'bearer',
                //     'expires_in' => JWTAuth::factory()->getTTL() * 60
                // ]
            ], 200)->withCookie($cookie);
        } catch (JWTException $e) {
            return response()->json(['error' => 'No se pudo crear el token'], 500);
        }
        // catch (ValidationException $e) {
        //     return response()->json(['error' => $e->validator->errors()->first()], 422);
        // }
        catch (\Exception $e) {
            // Cualquier otra excepción
            return response()->json(['error' => 'Error en el servidor. Por favor, inténtelo de nuevo más tarde.'], 500);
        }
    }

    public function register(Request $request){
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => [
                    'required',
                    'string',
                    Password::min(8) // Mínimo 8 caracteres de longitud
                     ->mixedCase() // Debe contener letras mayúsculas y minúsculas
                     ->numbers() // Debe contener al menos un número
                     ->symbols(), // Debe contener al menos un símbolo
                ],
            ],  [
                'email.unique' => 'El correo electrónico ya está registrado.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.password' => 'La contraseña debe incluir al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
            ]);

             // Crear un nuevo usuario
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
                 return response()->json(['error' => $e->validator->errors()->first()], 422);
        }catch (\Throwable $e) {
            if (strpos($e->getMessage(), 'SQLSTATE[23000]') !== false) {
                return response()->json(['error' => 'El correo electrónico ya está registrado.'], 422);
            }
            return response()->json(['error' => 'No se pudo registrar el usuario'], 500);
        }
    }
}
