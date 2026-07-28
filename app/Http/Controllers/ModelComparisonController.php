<?php

namespace App\Http\Controllers;

use App\Models\ModelVersion;
use App\Models\RecognitionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModelComparisonController extends Controller
{
    public function index(Request $request)
    {
        $query = RecognitionRecord::query()
            ->whereNotNull('model_version');

        if (! $request->user()->isAdmin()) {
            $query->whereBelongsTo($request->user());
        }

        $rows = $query
            ->select([
                'model_name',
                'model_version',
                DB::raw('count(*) as scans'),
                DB::raw('avg(confidence) as avg_confidence'),
                DB::raw('avg(processing_time_ms) as avg_time'),
                DB::raw("sum(case when confidence_level = 'high' then 1 else 0 end) as high_count"),
                DB::raw("sum(case when confidence_level = 'low' then 1 else 0 end) as low_count"),
                DB::raw("sum(case when recognition_status = 'failed' then 1 else 0 end) as failed_count"),
                DB::raw("sum(case when expert_species_id is not null and expert_species_id = crab_species_id then 1 else 0 end) as expert_matches"),
                DB::raw("sum(case when expert_species_id is not null then 1 else 0 end) as expert_reviews"),
            ])
            ->groupBy('model_name', 'model_version')
            ->orderByRaw('max(created_at) desc')
            ->get();

        return view('models.comparison', [
            'rows' => $rows,
            'registry' => ModelVersion::latest()->get(),
            'bestConfidence' => $rows->sortByDesc('avg_confidence')->first(),
            'fastest' => $rows->filter(fn ($row) => $row->avg_time !== null)->sortBy('avg_time')->first(),
        ]);
    }
}
