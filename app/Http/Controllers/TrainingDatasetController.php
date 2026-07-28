<?php

namespace App\Http\Controllers;

use App\Models\CrabSpecies;
use App\Models\RecognitionRecord;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrainingDatasetController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->query($request)->with(['species', 'expertSpecies', 'user'])->latest();
        if ($request->filled('status')) {
            $query->where('recognition_status', $request->status);
        }
        if ($request->filled('species')) {
            $query->where('expert_species_id', $request->species);
        }

        return view('training.index', [
            'records' => $query->paginate(10)->withQueryString(),
            'species' => CrabSpecies::where('is_active', true)->orderBy('common_name')->get(),
            'candidateCount' => (clone $this->query($request))->count(),
            'correctedCount' => (clone $this->query($request))->whereNotNull('expert_species_id')->count(),
            'lowCount' => (clone $this->query($request))->where('confidence_level', 'low')->count(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $records = $this->query($request)->with(['species', 'expertSpecies', 'user'])->latest()->get();

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Image path', 'AI class', 'Expert class', 'Confidence', 'Status', 'User', 'Reference']);
            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->original_image_path,
                    $record->species?->scientific_name ?? $record->predicted_class,
                    $record->expertSpecies?->scientific_name,
                    $record->confidence,
                    $record->recognition_status,
                    $record->user?->email,
                    $record->scan_reference,
                ]);
            }
            fclose($handle);
        }, 'training-dataset-candidates.csv', ['Content-Type' => 'text/csv']);
    }

    private function query(Request $request)
    {
        $query = RecognitionRecord::query()
            ->where(function ($inner) {
                $inner->where('needs_retraining', true)
                    ->orWhere('confidence_level', 'low')
                    ->orWhereNotNull('expert_species_id');
            });

        if (! $request->user()->isAdmin()) {
            $query->whereBelongsTo($request->user());
        }

        return $query;
    }
}
