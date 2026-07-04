<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with('latestMessage')
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('dashboard.conversations', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $conversation->load(['messages' => function ($query) {
            $query->orderBy('created_at');
        }]);

        return view('dashboard.conversation-detail', compact('conversation'));
    }
}
