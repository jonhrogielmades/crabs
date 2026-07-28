<?php

namespace App\Http\Controllers;

use App\Models\CrabSpecies;
use App\Models\RecognitionRecord;
use Illuminate\Http\Request;

class RecognitionMapController extends Controller
{
    public function index(Request $request)
    {
        $query = RecognitionRecord::with(['species', 'user'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest();

        if (! $request->user()->isAdmin()) {
            $query->whereBelongsTo($request->user());
        }
        if ($request->filled('species')) {
            $query->where('crab_species_id', $request->species);
        }
        if ($request->filled('confidence')) {
            $query->where('confidence_level', $request->confidence);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $records = $query->limit(200)->get();

        return view('recognition_map.index', [
            'records' => $records,
            'species' => CrabSpecies::where('is_active', true)->orderBy('common_name')->get(),
            'points' => $records->map(fn (RecognitionRecord $record) => [
                'reference' => $record->scan_reference,
                'species' => $record->species?->common_name ?? $record->predicted_class ?? 'Unknown',
                'confidence' => $record->confidence === null ? null : round($record->confidence * 100, 1),
                'level' => $record->confidence_level,
                'latitude' => $record->latitude,
                'longitude' => $record->longitude,
                'location' => $record->location_label,
                'url' => route('recognition.show', $record),
            ]),
        ]);
    }
}
