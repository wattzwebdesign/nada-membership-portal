<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function send(Request $request, ChatbotService $chatbot): JsonResponse
    {
        if (! config('chatbot.enabled')) {
            return response()->json(['error' => 'Chat is currently unavailable.'], 503);
        }

        $request->validate([
            'messages' => 'required|array|max:' . config('chatbot.max_messages', 20),
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:5000',
        ]);

        $ip = $request->ip();

        if ($chatbot->checkRateLimit($ip)) {
            return response()->json(['error' => 'Too many requests. Please try again later.'], 429);
        }

        $chatbot->hitRateLimit($ip);

        try {
            $response = $chatbot->sendMessage($request->input('messages'));

            return response()->json(['content' => $response]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => 'Something went wrong. Please try again.'], 500);
        }
    }
}
