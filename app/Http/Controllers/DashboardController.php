<?php

namespace App\Http\Controllers;

use App\Models\CrabSpecies;
use App\Models\RecognitionRecord;
use App\Services\AiServiceHealthService;

class DashboardController extends Controller
{
    public function index(AiServiceHealthService $health)
    {
        $records = RecognitionRecord::whereBelongsTo(auth()->user());
        return view('dashboard', [
            'recent' => (clone $records)->latest()->limit(5)->get(),
            'total' => (clone $records)->count(),
            'successful' => (clone $records)->where('recognition_status', 'recognized')->count(),
            'low' => (clone $records)->where('confidence_level', 'low')->count(),
            'speciesCount' => CrabSpecies::where('is_supported', true)->where('is_active', true)->count(),
            'aiStatus' => $health->status(),
        ]);
    }
}
