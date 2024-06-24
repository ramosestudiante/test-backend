<?php

namespace App\Helpers;

class ValidationRules
{
    public static function userRules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'rut' => 'required|string|max:12',
            'birthday' => 'required|date',
            'address' => 'required|string|max:255',
            'password' => [
                'required',
                'string',
                \Illuminate\Validation\Rules\Password::min(8) // Minimum 8 characters long
                    ->mixedCase() // Must contain upper and lower case letters
                    ->numbers() // Must contain at least one number
                    ->symbols(), // Must contain at least one symbol
            ],
        ];
    }

    public static function userMessages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'rut.required' => 'El RUT es obligatorio.',
            'birthday.required' => 'La fecha de nacimiento es obligatoria.',
            'birthday.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'address.required' => 'La dirección es obligatoria.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.password' => 'La contraseña debe incluir al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
        ];
    }
}
