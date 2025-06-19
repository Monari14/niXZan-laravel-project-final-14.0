<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Notifications\PostCommented;

class CommentController extends Controller
{
    public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post não encontrado.'], 404);
        }

        $comment = Comment::create([
            'post_id' => $postId,
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        // Notifica que o post foi comentado
        $post->user->notify(new PostCommented($request->user(), $post->id));

        return response()->json([
            'message' => 'Comentário adicionado com sucesso!',
            'comment' => $comment->load('user:id,username'),
        ]);
    }

    public function index($postId)
    {
        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post não encontrado.'], 404);
        }

        $comments = $post->comments()->with('user:id,username')->latest()->get();

        return response()->json($comments);
    }

    public function destroy(Request $request, $commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comentário não encontrado.'], 404);
        }

        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comentário deletado com sucesso.']);
    }
}
