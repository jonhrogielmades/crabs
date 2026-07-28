<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecognitionRequest;
use App\Models\CrabSpecies;
use App\Models\RecognitionRecord;
use App\Services\CrabRecognitionService;
use App\Services\ImageQualityService;
use App\Services\RecognitionReferenceService;
use App\Services\RecognitionGuidanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class RecognitionController extends Controller
{
    public function create() { return view('recognition.create'); }

    public function store(StoreRecognitionRequest $request, ImageQualityService $quality, CrabRecognitionService $recognizer)
    {
        $image = $request->file('image');
        $qualityResult = $quality->assess($image);
        $path = $image->store('recognitions/originals');
        $payload = null; $failure = null;
        try { $payload = $recognizer->predict($image); } catch (\Throwable $e) { report($e); $failure = $e->getMessage(); }

        $prediction = data_get($payload, 'prediction', []);
        $confidence = data_get($prediction, 'confidence');
        $predictedClass = data_get($prediction, 'class_name');
        $predictedClassId = data_get($prediction, 'class_id');
        $species = ($predictedClass || $predictedClassId) ? CrabSpecies::query()
            ->where(function ($query) use ($predictedClass, $predictedClassId) {
                if ($predictedClass) {
                    $query->where('scientific_name', $predictedClass)
                        ->orWhere('common_name', $predictedClass)
                        ->orWhere('model_class_name', $predictedClass);
                }

                if ($predictedClassId) {
                    $query->orWhere('model_class_id', $predictedClassId);
                }
            })
            ->first() : null;
        $status = $failure ? 'failed' : (($payload['success'] ?? false) ? 'recognized' : 'no_detection');
        if ($confidence !== null && RecognitionReferenceService::confidenceLevel((float) $confidence) === 'low') $status = 'low_confidence';

        $record = DB::transaction(fn () => RecognitionRecord::create([
            'user_id' => $request->user()->id,
            'crab_species_id' => $species?->id,
            'scan_reference' => RecognitionReferenceService::generate(),
            'original_image_path' => $path,
            'predicted_class' => $predictedClass,
            'confidence' => $confidence,
            'confidence_level' => RecognitionReferenceService::confidenceLevel($confidence === null ? null : (float) $confidence),
            'recognition_status' => $status,
            'blur_score' => data_get($payload, 'image_quality.blur_score', $qualityResult['blur_score']),
            'brightness_score' => data_get($payload, 'image_quality.brightness_score', $qualityResult['brightness_score']),
            'quality_warnings' => array_values(array_unique(array_merge($qualityResult['warnings'], data_get($payload, 'image_quality.warnings', [])))),
            'bounding_box' => data_get($prediction, 'bounding_box'),
            'processing_time_ms' => data_get($payload, 'processing_time_ms'),
            'model_name' => data_get($payload, 'model.name'),
            'model_version' => data_get($payload, 'model.version'),
            'ai_response' => $payload,
            'failure_reason' => $failure,
            'latitude' => $request->filled('latitude') ? $request->input('latitude') : null,
            'longitude' => $request->filled('longitude') ? $request->input('longitude') : null,
            'location_label' => $request->input('location_label'),
            'capture_notes' => $request->input('capture_notes'),
        ]));

        return redirect()->route('recognition.show', $record);
    }

    public function show(RecognitionRecord $recognitionRecord, RecognitionGuidanceService $guidance)
    {
        abort_unless(auth()->user()->isAdmin() || $recognitionRecord->user_id === auth()->id(), 403);
        $recognitionRecord->load(['species', 'expertSpecies', 'feedback']);

        return view('recognition.show', ['record' => $recognitionRecord, 'guidance' => $guidance->for($recognitionRecord)]);
    }

    public function image(RecognitionRecord $recognitionRecord): Response
    {
        abort_unless(auth()->user()->isAdmin() || $recognitionRecord->user_id === auth()->id(), 403);
        abort_unless(Storage::exists($recognitionRecord->original_image_path), 404);
        return response(Storage::get($recognitionRecord->original_image_path), 200, ['Content-Type' => Storage::mimeType($recognitionRecord->original_image_path) ?? 'image/jpeg']);
    }

    public function destroy(RecognitionRecord $recognitionRecord)
    {
        abort_unless($recognitionRecord->user_id === auth()->id(), 403);
        Storage::delete([$recognitionRecord->original_image_path, $recognitionRecord->annotated_image_path]);
        $recognitionRecord->delete();
        return redirect()->route('recognition.history')->with('status', 'Recognition record deleted.');
    }
}
