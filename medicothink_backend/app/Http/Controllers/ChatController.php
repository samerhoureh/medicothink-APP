<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function sendMessage(Request $request)
    {
        // Mock response for now
        return response()->json([
            'success' => true,
            'response' => 'This is a mock AI response. Please integrate with OpenAI service.',
            'conversation_id' => $request->conversation_id
        ]);
    }

    public function analyzeImage(Request $request)
    {
        // Mock response for now
        return response()->json([
            'success' => true,
            'analysis' => 'This is a mock image analysis. Please integrate with AI service.',
            'conversation_id' => $request->conversation_id
        ]);
    }

    public function getConversations(Request $request)
    {
        return response()->json([
            'success' => true,
            'conversations' => []
        ]);
    }

    public function getConversation(Request $request, $conversationId)
    {
        return response()->json([
            'success' => true,
            'conversation' => null,
            'messages' => []
        ]);
    }

    public function archiveConversation(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Conversation archived successfully'
        ]);
    }

    public function unarchiveConversation(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Conversation unarchived successfully'
        ]);
    }

    public function deleteConversation(Request $request, $conversationId)
    {
        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully'
        ]);
    }

    public function getConversationSummary(Request $request, $conversationId)
    {
        return response()->json([
            'success' => true,
            'summary' => 'Mock conversation summary'
        ]);
    }
}