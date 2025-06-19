<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function getUserByUsername($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'avatar' => $user->avatar ? asset('s/' . $user->avatar) : null,
            'created_at' => $user->created_at,
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // até 2MB
        ]);

        $user = $request->user();

        // Apaga avatar antigo, se tiver (opcional)
        if ($user->avatar && Storage::disk('p')->exists($user->avatar)) {
            Storage::disk('p')->delete($user->avatar);
        }

        // Salva o arquivo novo
        $path = $request->file('avatar')->store('avatars', 'public');

        // Atualiza o campo avatar no DB
        $user->avatar = $path;
        $user->save();

        return response()->json([
            'message' => 'Foto de perfil atualizada com sucesso!',
            'avatar_url' => asset('s/' . $path),
        ]);
    }
}
