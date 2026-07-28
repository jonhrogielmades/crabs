<?php

namespace App\Http\Controllers;

use App\Models\CrabSpecies;
use App\Models\RecognitionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecognitionHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = RecognitionRecord::whereBelongsTo($request->user())->with(['species', 'expertSpecies'])->withCount('feedback')->latest();
        if ($request->filled('q')) {
            $query->where(function ($inner) use ($request) {
                $term = '%'.$request->q.'%';
                $inner->where('scan_reference', 'like', $term)
                    ->orWhere('predicted_class', 'like', $term)
                    ->orWhere('location_label', 'like', $term);
            });
        }
        if ($request->filled('species')) $query->where('crab_species_id', $request->species);
        if ($request->filled('confidence')) $query->where('confidence_level', $request->confidence);
        return view('recognition.history', ['records' => $query->paginate(12)->withQueryString(), 'species' => CrabSpecies::where('is_active', true)->orderBy('common_name')->get()]);
    }

    public function clear(Request $request)
    {
        $records = RecognitionRecord::whereBelongsTo($request->user())->get(['id', 'original_image_path', 'annotated_image_path']);
        $paths = $records->flatMap(fn (RecognitionRecord $record) => [$record->original_image_path, $record->annotated_image_path])->filter()->all();

        Storage::delete($paths);
        RecognitionRecord::whereKey($records->pluck('id'))->delete();

        return redirect()->route('recognition.history')->with('status', 'Recognition history cleared.');
    }
}
