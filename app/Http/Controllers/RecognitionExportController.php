<?php

namespace App\Http\Controllers;

use App\Models\RecognitionRecord;
use App\Services\SimplePdfService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecognitionExportController extends Controller
{
    public function csv(Request $request): StreamedResponse
    {
        $records = $this->records($request)->get();

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Scan reference', 'Date', 'Detected species', 'Expert species', 'Confidence', 'Status', 'Location', 'Model', 'Retraining']);
            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->scan_reference,
                    optional($record->created_at)->toDateTimeString(),
                    $record->species?->common_name ?? $record->predicted_class ?? 'Unknown',
                    $record->expertSpecies?->common_name,
                    $record->confidence === null ? '' : number_format($record->confidence * 100, 1).'%',
                    $record->recognition_status,
                    $record->location_label ?: trim(($record->latitude ?? '').' '.($record->longitude ?? '')),
                    trim(($record->model_name ?? '').' '.($record->model_version ?? '')),
                    $record->needs_retraining ? 'yes' : 'no',
                ]);
            }
            fclose($handle);
        }, 'recognition-history.csv', ['Content-Type' => 'text/csv']);
    }

    public function pdf(Request $request, SimplePdfService $pdf)
    {
        $records = $this->records($request)->limit(200)->get();
        $lines = [
            'Generated: '.now()->toDateTimeString(),
            'User: '.$request->user()->name,
            'Records: '.$records->count(),
            '',
        ];

        foreach ($records as $record) {
            $lines[] = implode(' | ', array_filter([
                $record->scan_reference,
                optional($record->created_at)->format('Y-m-d H:i'),
                $record->species?->common_name ?? $record->predicted_class ?? 'Unknown',
                $record->confidence === null ? 'Confidence N/A' : 'Confidence '.number_format($record->confidence * 100, 1).'%',
                ucfirst(str_replace('_', ' ', $record->recognition_status)),
                $record->location_label ?: null,
                $record->needs_retraining ? 'Training candidate' : null,
            ]));
        }

        return response($pdf->textReport('CrabAI Recognition History', $lines), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="recognition-history.pdf"',
        ]);
    }

    private function records(Request $request)
    {
        $query = RecognitionRecord::with(['species', 'expertSpecies'])->latest();
        if (! $request->user()->isAdmin()) {
            $query->whereBelongsTo($request->user());
        }
        if ($request->filled('q')) {
            $query->where(function ($inner) use ($request) {
                $inner->where('scan_reference', 'like', '%'.$request->q.'%')
                    ->orWhere('predicted_class', 'like', '%'.$request->q.'%')
                    ->orWhere('location_label', 'like', '%'.$request->q.'%');
            });
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

        return $query;
    }
}
