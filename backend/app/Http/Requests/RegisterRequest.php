<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'El correo electrónico ya está registrado.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }

    protected function failedValidation(ValidatorContract $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Error de validación.',
            'errors' => $validator->errors(),
        ], 422));
    }
}