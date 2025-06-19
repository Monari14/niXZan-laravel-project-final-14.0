<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Cadastro
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:50',
                'username' => 'required|string|max:30|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'avatar' => '/i/a/default.png',
            ]);

            $token = $user->createToken('TOKEN')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'error' => 'Ocorreu um erro ao registrar o usuário.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Login
    public function login(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required|string', // pode ser email ou username
                'password' => 'required|string',
            ]);

            // Buscar por email ou username
            $user = User::where('email', $request->login)
                ->orWhere('username', $request->login)
                ->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'login' => ['As credenciais estão incorretas.'],
                ]);
            }

            $token = $user->createToken('TOKEN')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 200);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'error' => 'Ocorreu um erro ao realizar o login.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
