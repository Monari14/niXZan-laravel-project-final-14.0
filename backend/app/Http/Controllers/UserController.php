<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        // Salva o novo avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        // Atualiza no banco
        $user->avatar = $path;
        $user->save();

        return response()->json([
            'message' => 'Foto de perfil atualizada com sucesso!',
            'avatar_url' => asset('s/' . $path),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $request->input('name', $user->name),
            'bio' => $request->input('bio', $user->bio),
        ]);

        return response()->json([
            'message' => 'Perfil atualizado com sucesso!',
            'user' => $user
        ]);
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        // Verificação de senha (opcional)
        if ($request->has('password') && !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Senha incorreta.'], 403);
        }

        // Deleta avatar do storage
        if ($user->avatar && Storage::disk('p')->exists($user->avatar)) {
            Storage::disk('p')->delete($user->avatar);
        }

        $user->delete();

        return response()->json(['message' => 'Conta excluída com sucesso.']);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'name' => $user->name,
            'bio' => $user->bio,
            'avatar' => $user->avatar ? asset('s/' . $user->avatar) : null,
            'created_at' => $user->created_at,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed', // exige new_password_confirmation
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Senha atual incorreta.'], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Senha atualizada com sucesso.']);
    }

    public function follow(Request $request, $username)
    {
        $userToFollow = User::where('username', $username)->first();

        if (!$userToFollow) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        if ($request->user()->id === $userToFollow->id) {
            return response()->json(['message' => 'Você não pode seguir a si mesmo.'], 400);
        }

        $alreadyFollowing = Follower::where('follower_id', $request->user()->id)
            ->where('following_id', $userToFollow->id)
            ->exists();

        if ($alreadyFollowing) {
            return response()->json(['message' => 'Você já está seguindo este usuário.'], 400);
        }

        Follower::create([
            'follower_id' => $request->user()->id,
            'following_id' => $userToFollow->id,
        ]);

        return response()->json(['message' => 'Agora você está seguindo ' . $username]);
    }

    public function unfollow(Request $request, $username)
    {
        $userToUnfollow = User::where('username', $username)->first();

        if (!$userToUnfollow) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        Follower::where('follower_id', $request->user()->id)
            ->where('following_id', $userToUnfollow->id)
            ->delete();

        return response()->json(['message' => 'Você deixou de seguir ' . $username]);
    }

    public function followers($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $followers = $user->followers()->with('follower:id,username')->get()->pluck('follower');

        return response()->json($followers);
    }

    public function following($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $following = $user->following()->with('following:id,username')->get()->pluck('following');

        return response()->json($following);
    }
}
