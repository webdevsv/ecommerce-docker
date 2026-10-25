<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/register",
        tags: ["Autenticacion"],
        summary: "Registrar un nuevo cliente",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Cliente Prueba"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "prueba@correo.com"),
                    new OA\Property(property: "password", type: "string", example: "12345678"),
                    new OA\Property(property: "password_confirmation", type: "string", example: "12345678"),
                    new OA\Property(property: "phone", type: "string", example: "70001234"),
                    new OA\Property(property: "address", type: "string", example: "San Salvador"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Cliente registrado exitosamente"),
            new OA\Response(response: 422, description: "Error de validacion"),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registro exitoso.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    #[OA\Post(
        path: "/login",
        tags: ["Autenticacion"],
        summary: "Iniciar sesion y obtener un token de acceso",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "prueba@correo.com"),
                    new OA\Property(property: "password", type: "string", example: "12345678"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Autenticacion exitosa"),
            new OA\Response(response: 401, description: "Credenciales invalidas"),
            new OA\Response(response: 422, description: "Error de validacion"),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Las credenciales proporcionadas son incorrectas.',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesion exitoso.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    #[OA\Post(
        path: "/logout",
        tags: ["Autenticacion"],
        summary: "Cerrar sesion (revoca el token actual)",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Sesion cerrada exitosamente"),
            new OA\Response(response: 401, description: "No autenticado"),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesion cerrada exitosamente.',
        ]);
    }

    #[OA\Get(
        path: "/me",
        tags: ["Autenticacion"],
        summary: "Obtener el perfil del usuario autenticado",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Perfil del usuario"),
            new OA\Response(response: 401, description: "No autenticado"),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }
}