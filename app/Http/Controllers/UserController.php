<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
  public function list(Request $request)
  {

    try {
      // Obtengo a todos los usuarios
      $users = User::all();

      return response()->json([
        'status' => 'success',
        'users' => $users,
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'No se pudo obtener la lista de usuarios',
        'error' => $e->getMessage(),
      ], 500);
    }
  }

  public function create(Request $request)
  {
    try {

      // valido los campos de name, email y password con sus reglas de validacion
      $request->validate([
        'name' => 'required|string',
        'email' => 'required|string|email|unique:users',
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

      // Crear un nuevo usuario
      $user = new User();
      $user->name = $request->input('name');
      $user->email = $request->input('email');
      $user->password = Hash::make($request->input('password'));
      $user->role_id = $request->input('role_id', 2);
      $user->save();

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

  public function delete($id)
  {
    try {
      // busco al usuario con findorFail y busco por el id de la ruta ingresada una vez encontrado se elimina el usuario 
      $user = User::findOrFail($id);
      $user->delete();

      return response()->json([
        'status' => 'success',
        'user' => $user,
        'message' => 'Usuario eliminado correctamente'
      ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      // Handle if the user is not found
      return response()->json(['error' => 'Usuario no encontrado'], 404);
    } catch (\Throwable $th) {
      // Handle other exceptions, including the one thrown by findOrFail
      return response()->json(['error' => 'Error al eliminar el usuario', 'message' => $th->getMessage()], 500);
    }
  }


  public function update(Request $request, $id)
  {
    try {
      // busco al usuario con findorFail y busco por el id de la ruta ingresada
      $user = User::findOrFail($id);

      // valido los campos de la solicitud a modificar
      // valido los campos de name, email y password con sus reglas de validacion 
      $validatedData = $request->validate([
        'name' => 'string',
        'email' => 'string|email|unique:users',
        'password' => [
          'string',
          Password::min(8) // Mínimo 8 caracteres de longitud
            ->mixedCase() // Debe contener letras mayúsculas y minúsculas
            ->numbers() // Debe contener al menos un número
            ->symbols(), // Debe contener al menos un símbolo
        ],
      ],  [
        'email.unique' => 'El correo electrónico ya está registrado.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.password' => 'La contraseña debe incluir al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
      ]);

      // Actualizo la password del usuario si existe el campo
      if (isset($validatedData['password'])) {
        $validatedData['password'] = bcrypt($validatedData['password']);
      }
      // actualizo el usuario de acuerdo de que si pasan las reglas de validacion
      $user->update($validatedData);

      return response()->json([
        'status' => 'success',
        'user' => $user,
        'message' => 'Usuario actualizado correctamente'
      ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      // Manejar la excepción si el usuario no se encuentra
      return response()->json(['error' => 'Usuario no encontrado'], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
      // Manejar errores de validación
      return response()->json(['errors' => $e->errors()], 422);
    } catch (\Throwable $th) {
      // Manejar cualquier otra excepción
      return response()->json(['error' => 'Error al actualizar el usuario', 'message' => $th->getMessage()], 500);
    }
  }

  public function show($id)
  {
    try {
      // Me devuelve el registro del usuario buscado por id 
      $user = User::findOrFail($id);
      return response()->json([
        'user' => $user,
        'message' => 'Usuario obtenido correctamente'
      ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      // Maneja la excepción si el usuario no se encuentra
      return response()->json(['error' => 'Usuario no encontrado'], 404);
    } catch (\Throwable $th) {
      // Maneja cualquier otra excepción
      return response()->json(['error' => 'Error al obtener el usuario', 'message' => $th->getMessage()], 500);
    }
  }
}
