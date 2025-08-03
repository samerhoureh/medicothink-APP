<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        $conversations = Conversation::where('user_id', $user->id)
            ->when($request->archived === 'true', function ($query) {
                return $query->archived();
            }, function ($query) {
                return $query->active();
            })
            ->with(['latestMessage'])
            ->orderBy('last_message_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'conversations' => ConversationResource::collection($conversations->items()),
                'pagination' => [
                    'current_page' => $conversations->currentPage(),
                    'last_page' => $conversations->lastPage(),
                    'per_page' => $conversations->perPage(),
                    'total' => $conversations->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $conversation = Conversation::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => new ConversationResource($conversation),
                'messages' => MessageResource::collection($conversation->messages),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $user = $request->user();
        
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'title' => $request->title,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation created successfully',
            'data' => [
                'conversation' => new ConversationResource($conversation),
            ],
        ], 201);
    }

    public function archive(Request $request, $id)
    {
        $user = $request->user();
        
        $conversation = Conversation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $conversation->archive();

        return response()->json([
            'success' => true,
            'message' => 'Conversation archived successfully',
        ]);
    }

    public function unarchive(Request $request, $id)
    {
        $user = $request->user();
        
        $conversation = Conversation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $conversation->unarchive();

        return response()->json([
            'success' => true,
            'message' => 'Conversation unarchived successfully',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        $conversation = Conversation::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully',
        ]);
    }
}