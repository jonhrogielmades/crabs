<?php

namespace App\Http\Controllers;

use App\Models\CrabSpecies;
use App\Services\CrabInfoChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrabChatController extends Controller
{
    public function index()
    {
        return view('crab-chat.index', [
            'suggestedSpecies' => CrabSpecies::where('is_active', true)->orderBy('common_name')->limit(3)->get(),
        ]);
    }

    public function chat(Request $request, CrabInfoChatService $chat): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        return response()->json($chat->answer($data['message']));
    }
}
