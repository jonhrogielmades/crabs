<?php

namespace App\Http\Controllers;

use App\Models\RecognitionFeedback;
use App\Models\RecognitionRecord;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request, RecognitionRecord $recognitionRecord)
    {
        abort_unless($recognitionRecord->user_id === $request->user()->id, 403);
        $data = $request->validate(['category' => ['required', 'in:incorrect_prediction,unclear_result,unsupported_crab,technical_issue,image_processing_failure,other'], 'description' => ['required', 'string', 'max:2000']]);
        RecognitionFeedback::create($data + ['recognition_record_id' => $recognitionRecord->id, 'user_id' => $request->user()->id]);
        return back()->with('status', 'Feedback submitted for administrator review.');
    }
}
