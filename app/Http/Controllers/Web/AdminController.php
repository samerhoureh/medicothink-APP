<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Subscription;
use App\Models\PaymentTransaction;
use App\Models\AppVersion;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'total_conversations' => Conversation::count(),
            'total_messages' => Message::count(),
            'active_subscriptions' => Subscription::active()->count(),
            'expired_subscriptions' => Subscription::expired()->count(),
            'total_revenue' => PaymentTransaction::completed()->sum('amount'),
            'monthly_revenue' => PaymentTransaction::completed()
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];

        $recentUsers = User::latest()->limit(5)->get();
        $recentTransactions = PaymentTransaction::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentTransactions'));
    }

    public function users(Request $request)
    {
        $query = User::with('subscription');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function userDetails($id)
    {
        $user = User::with(['subscription', 'conversations', 'paymentTransactions'])
            ->findOrFail($id);
        
        return view('admin.users.show', compact('user'));
    }

    public function conversations(Request $request)
    {
        $query = Conversation::with(['user', 'messages']);

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $conversations = $query->paginate(20);
        return view('admin.conversations.index', compact('conversations'));
    }

    public function conversationDetails($id)
    {
        $conversation = Conversation::with(['user', 'messages', 'summary'])
            ->findOrFail($id);
        
        return view('admin.conversations.show', compact('conversation'));
    }

    public function subscriptions(Request $request)
    {
        $query = Subscription::with('user');

        if ($request->status) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'expired':
                    $query->expired();
                    break;
                case 'expiring_soon':
                    $query->expiringSoon();
                    break;
            }
        }

        $subscriptions = $query->paginate(20);
        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function payments(Request $request)
    {
        $query = PaymentTransaction::with(['user', 'subscription']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->gateway) {
            $query->where('payment_gateway', $request->gateway);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.payments.index', compact('payments'));
    }

    public function appVersions()
    {
        $versions = AppVersion::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.app-versions.index', compact('versions'));
    }

    public function createAppVersion(Request $request)
    {
        $request->validate([
            'version_name' => 'required|string',
            'version_code' => 'required|integer',
            'platform' => 'required|in:android,ios',
            'download_url' => 'required|url',
            'is_required' => 'boolean',
            'release_notes' => 'array',
        ]);

        AppVersion::create($request->all());

        return redirect()->route('admin.app-versions')
            ->with('success', 'App version created successfully');
    }

    public function settings()
    {
        $settings = [
            'app_name' => config('app.name'),
            'app_version' => '1.0.0',
            'maintenance_mode' => false,
            'registration_enabled' => true,
            'ai_features_enabled' => true,
        ];

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        // Update application settings
        // This would typically update a settings table or config files
        
        return redirect()->route('admin.settings')
            ->with('success', 'Settings updated successfully');
    }

    public function analytics()
    {
        $analytics = [
            'daily_active_users' => User::whereDate('updated_at', today())->count(),
            'weekly_active_users' => User::where('updated_at', '>=', now()->subWeek())->count(),
            'monthly_active_users' => User::where('updated_at', '>=', now()->subMonth())->count(),
            'conversations_today' => Conversation::whereDate('created_at', today())->count(),
            'messages_today' => Message::whereDate('created_at', today())->count(),
            'revenue_today' => PaymentTransaction::completed()
                ->whereDate('created_at', today())
                ->sum('amount'),
        ];

        return view('admin.analytics', compact('analytics'));
    }
}