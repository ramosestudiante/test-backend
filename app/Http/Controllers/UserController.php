<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Helpers\ValidationRules;
use App\Models\Role;


class UserController extends Controller
{
  public function list(Request $request)
  {
    try {
      $authenticatedUserId = auth()->user()->id;
      // Obtener los parámetros de la solicitud
      
      // the first page
      $search = $request->query('search'); 
      $page = $request->query('page', 1);
      $perPage = $request->query('per_page'); // 10 user per page
      // querys
      $query = User::query();
      $query->where('id', '!=', $authenticatedUserId);

      if ($search) {
        $query->where(function($query) use ($search) {
          $query->where('name', 'like', '%' . $search . '%')
          ->orWhere('rut', 'like', '%' . $search . '%')
          ->orWhere('address', 'like', '%' . $search . '%');
        });
      }
      
      // paginate
      $users = $query->paginate($perPage, ['*'], 'page', $page);

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
      $request->validate(
        ValidationRules::userRules(),
        ValidationRules::userMessages()
      );
      // Validate RUT
      if (!validarRut($request->input('rut'))) {
        return response()->json(['error' => 'El RUT ingresado no es válido.'], 422);
      }

      $user = new User();
      $user->name = $request->input('name');
      $user->email = $request->input('email');
      $user->rut = $request->input('rut');
      $user->birthday = $request->input('birthday');
      $user->address = $request->input('address');
      $user->password = Hash::make($request->input('password'));
      $user->role_id = $request->input('role_id', Role::USER);
      $user->save();

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
      // I search for the user with findorFail and search by the id of the entered route. Once found, the user is deleted
      $user = User::findOrFail($id);
      $user->delete();

      return response()->json([
        'status' => 'success',
        'user' => $user,
        'message' => 'Usuario eliminado correctamente'
      ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

      return response()->json(['error' => 'Usuario no encontrado'], 404);
    } catch (\Throwable $th) {
      return response()->json(['error' => 'Error al eliminar el usuario', 'message' => $th->getMessage()], 500);
    }
  }


  public function update(Request $request, $id)
  {
    try {

      // search for the user with findorFail and search by the id of the entered route
      $user = User::findOrFail($id);

      // validate the request fields to modify
      // validate the name, email and password fields with their validation rules
      
      // Validate the RUT
       // Validar el RUT solo si se envía en la solicitud y ha cambiado
       if ($request->has('rut') && $request->input('rut') !== $user->rut) {
        if (!validarRut($request->input('rut'))) {
            return response()->json(['error' => 'El RUT ingresado no es válido.'], 422);
        }
    }

      $validatedData = $request->validate([
        'name' => 'string',
        'email' => 'string|email|unique:users',
        'address' => 'string',
        'birthday' => 'date',
        'rut' => 'string|unique:users',
        'role_id' => 'integer'
      ],  [
        'rut.unique' => 'El Rut ya existe',
        'email.unique' => 'El correo electrónico ya está registrado.',
      ]);
      // update the user according to whether they pass the validation rules
      $user->update($validatedData);

      return response()->json([
        'status' => 'success',
        'user' => $user,
        'message' => 'Usuario actualizado correctamente'
      ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      return response()->json(['error' => 'Usuario no encontrado'], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json(['errors' => $e->errors()], 422);
    } catch (\Throwable $th) {
      return response()->json(['error' => 'Error al actualizar el usuario', 'message' => $th->getMessage()], 500);
    }
  }

  public function show($id)
  {
    try {
      // Returns the record of the user searched by id
      $user = User::findOrFail($id);
      return response()->json([
        'user' => $user,
        'message' => 'Usuario obtenido correctamente'
      ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      return response()->json(['error' => 'Usuario no encontrado'], 404);
    } catch (\Throwable $th) {
      return response()->json(['error' => 'Error al obtener el usuario', 'message' => $th->getMessage()], 500);
    }
  }
}
