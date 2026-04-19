<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Info(title: "Smart Attendance API", version: "1.0.0", description: "API for managing smart attendance with QR code generation and JWT authentication.")]
#[OA\Server(url: "http://localhost:8000", description: "Local Development Server")]
#[OA\SecurityScheme(securityScheme: "bearerAuth", type: "http", scheme: "bearer", bearerFormat: "JWT")]
class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/auth/login",
        summary: "User Login",
        description: "Authenticate user and return JWT token.",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful login",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
                        new OA\Property(property: "role", type: "string", example: "admin")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Unauthorized")
                    ]
                )
            )
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'token' => $token,
            'role' => auth('api')->user()->role
        ]);
    }
}
