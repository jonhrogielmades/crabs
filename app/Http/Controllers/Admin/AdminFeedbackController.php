<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CrabSpecies;
use App\Models\RecognitionFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $query = RecognitionFeedback::with(['user', 'recognitionRecord.species', 'recognitionRecord.expertSpecies'])->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->boolean('training_candidates')) {
            $query->whereHas('recognitionRecord', fn ($inner) => $inner->where('needs_retraining', true));
        }

        return view('admin.feedback.index', [
            'feedback' => $query->paginate(10)->withQueryString(),
            'species' => CrabSpecies::where('is_active', true)->orderBy('common_name')->get(),
            'statuses' => ['open', 'reviewing', 'training_candidate', 'resolved', 'dismissed'],
            'categories' => ['incorrect_prediction', 'unclear_result', 'unsupported_crab', 'technical_issue', 'image_processing_failure', 'other'],
        ]);
    }

    public function update(Request $request, RecognitionFeedback $recognitionFeedback)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,reviewing,training_candidate,resolved,dismissed'],
            'admin_response' => ['nullable', 'string', 'max:2000'],
            'expert_species_id' => ['nullable', 'exists:crab_species,id'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $recognitionFeedback, $data) {
            $record = $recognitionFeedback->recognitionRecord()->lockForUpdate()->firstOrFail();
            $oldFeedback = $recognitionFeedback->toArray();
            $oldRecord = $record->toArray();
            $isClosed = in_array($data['status'], ['resolved', 'dismissed'], true);

            $recognitionFeedback->update([
                'status' => $data['status'],
                'admin_response' => $data['admin_response'] ?? null,
                'resolved_by' => $isClosed ? $request->user()->id : null,
                'resolved_at' => $isClosed ? now() : null,
            ]);

            $record->update([
                'expert_species_id' => $data['expert_species_id'] ?? null,
                'needs_retraining' => $request->boolean('needs_retraining') || $data['status'] === 'training_candidate',
                'admin_notes' => $data['admin_notes'] ?? null,
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'feedback.reviewed',
                'entity_type' => RecognitionFeedback::class,
                'entity_id' => $recognitionFeedback->id,
                'old_values' => ['feedback' => $oldFeedback, 'record' => $oldRecord],
                'new_values' => ['feedback' => $recognitionFeedback->fresh()->toArray(), 'record' => $record->fresh()->toArray()],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route('admin.feedback.index')->with('status', 'Feedback review saved.');
    }
}
