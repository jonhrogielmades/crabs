<?php

namespace App\Http\Controllers;

use App\Models\CrabSpecies;
use App\Models\RecognitionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsPageController extends Controller
{
    public function index(Request $request)
    {
        $query = RecognitionRecord::with('species')->latest();
        if (! $request->user()->isAdmin()) {
            $query->whereBelongsTo($request->user());
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('species')) {
            $query->where('crab_species_id', $request->species);
        }
        if ($request->filled('confidence')) {
            $query->where('confidence_level', $request->confidence);
        }

        $base = clone $query;
        $records = $query->paginate(10)->withQueryString();

        return view('reports.index', [
            'records' => $records,
            'species' => CrabSpecies::where('is_active', true)->orderBy('common_name')->get(),
            'total' => (clone $base)->count(),
            'recognized' => (clone $base)->where('recognition_status', 'recognized')->count(),
            'low' => (clone $base)->where('confidence_level', 'low')->count(),
            'avgConfidence' => (clone $base)->avg('confidence'),
            'byStatus' => (clone $base)->select('recognition_status', DB::raw('count(*) as total'))->groupBy('recognition_status')->pluck('total', 'recognition_status'),
        ]);
    }
}
