<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\FacebookSetting;
use App\Models\Message;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $facebookSetting = FacebookSetting::where('user_id', $user->id)->first();

        $todayMessages = Message::whereDate('created_at', today())->count();
        $totalConversations = Conversation::count();
        $totalMessages = Message::count();
        $aiReplies = Message::where('direction', 'outgoing')->count();

        $recentConversations = Conversation::with('latestMessage')
            ->orderBy('last_message_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'facebookSetting',
            'todayMessages',
            'totalConversations',
            'totalMessages',
            'aiReplies',
            'recentConversations'
        ));
    }

    public function integration()
    {
        return view('dashboard.integration');
    }

    public function facebookPost()
    {
        return view('dashboard.facebook');
    }

    public function settings()
    {
        return view('dashboard.settings');
    }

    public function leads()
    {
        return view('dashboard.leads');
    }

    public function reports()
    {
        return view('dashboard.reports');
    }

    public function whatsapp()
    {
        return view('dashboard.whatsapp');
    }

    public function inventory()
    {
        return view('dashboard.inventory');
    }

    public function inventoryAdd()
    {
        return view('dashboard.inventory-add');
    }
}
