<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Subscription;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'total_conversations' => Conversation::count(),
            'total_messages' => Message::count(),
            'active_subscriptions' => Subscription::active()->count(),
            'expired_subscriptions' => Subscription::expired()->count(),
        ];

        $recentUsers = User::latest()->limit(5)->get();
        $recentConversations = Conversation::with('user')->latest()->limit(5)->get();

        return view('dashboard.index', compact('stats', 'recentUsers', 'recentConversations'));
    }

    public function users()
    {
        $users = User::with('subscription')->paginate(20);
        return view('dashboard.users.index', compact('users'));
    }

    public function conversations()
    {
        $conversations = Conversation::with(['user', 'messages'])->paginate(20);
        return view('dashboard.conversations.index', compact('conversations'));
    }

    public function subscriptions()
    {
        $subscriptions = Subscription::with('user')->paginate(20);
        return view('dashboard.subscriptions.index', compact('subscriptions'));
    }

    public function settings()
    {
        return view('dashboard.settings');
    }
}