<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Notifications\PostLiked;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user:id,username,avatar')
                    ->withCount('likes')
                    ->latest()
                    ->get();

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
            'image' => $path,
        ]);

        return response()->json([
            'message' => 'Post criado com sucesso!',
            'post' => $post
        ]);
    }

    public function show($id)
    {
        $post = Post::with('user:id,username,avatar', 'likes', 'comments.user:id,username')
                    ->withCount('likes')
                    ->find($id);

        if (!$post) {
            return response()->json(['message' => 'Post não encontrado.'], 404);
        }

        return response()->json($post);
    }

    public function destroy(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post || $post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado ou post não existe.'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deletado com sucesso.']);
    }

    public function like(Request $request, $postId)
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post não encontrado.'], 404);
        }

        $alreadyLiked = $post->likes()->where('user_id', $request->user()->id)->exists();

        if ($alreadyLiked) {
            return response()->json(['message' => 'Você já curtiu este post.'], 400);
        }

        $post->likes()->create([
            'user_id' => $request->user()->id,
        ]);

        // Notifica o like
        $post->user->notify(new PostLiked($request->user(), $post->id));

        return response()->json(['message' => 'Post curtido!']);
    }

    public function unlike(Request $request, $postId)
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post não encontrado.'], 404);
        }

        $like = $post->likes()->where('user_id', $request->user()->id)->first();

        if (!$like) {
            return response()->json(['message' => 'Você não curtiu este post.'], 400);
        }

        $like->delete();

        return response()->json(['message' => 'Curtida removida.']);
    }

    public function feed(Request $request)
    {
        $user = $request->user();
        $followingIds = $user->following()->pluck('following_id');

        // Buscar posts de usuários que o usuário segue e que não sejam privados sem permissão
        $posts = Post::with('user:id,username,avatar')
            ->whereIn('user_id', $followingIds)
            ->whereHas('user', function ($q) use ($user) {
                $q->where('is_private', false)
                ->orWhere('id', $user->id); // Inclui posts do próprio usuário mesmo que privado
            })
            ->withCount('likes')
            ->latest()
            ->paginate(10);

        return response()->json($posts);
    }
}
