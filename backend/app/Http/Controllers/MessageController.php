<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    // Enviar mensagem para outro usuário
    public function send(Request $request, $receiverUsername)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $receiver = User::where('username', $receiverUsername)->first();
        if (!$receiver) {
            return response()->json(['message' => 'Usuário destinatário não encontrado'], 404);
        }

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $receiver->id,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Mensagem enviada com sucesso', 'data' => $message]);
    }

    // Listar mensagens trocadas com um usuário (chat)
    public function conversation(Request $request, $username)
    {
        $otherUser = User::where('username', $username)->first();
        if (!$otherUser) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $userId = $request->user()->id;
        $otherUserId = $otherUser->id;

        $messages = Message::where(function ($q) use ($userId, $otherUserId) {
            $q->where('sender_id', $userId)->where('receiver_id', $otherUserId);
        })->orWhere(function ($q) use ($userId, $otherUserId) {
            $q->where('sender_id', $otherUserId)->where('receiver_id', $userId);
        })->orderBy('created_at')->get();

        return response()->json($messages);
    }

    // Marcar mensagem como lida
    public function markAsRead(Request $request, $messageId)
    {
        $message = Message::where('id', $messageId)
            ->where('receiver_id', $request->user()->id)
            ->first();

        if (!$message) {
            return response()->json(['message' => 'Mensagem não encontrada'], 404);
        }

        $message->read_at = now();
        $message->save();

        return response()->json(['message' => 'Mensagem marcada como lida']);
    }
}
