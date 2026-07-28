<?php

namespace App\Services;

use App\Models\RecognitionRecord;

class RecognitionGuidanceService
{
    public function for(RecognitionRecord $record): array
    {
        $items = [];

        if ($record->recognition_status === 'failed') {
            $items[] = ['title' => 'AI service check', 'detail' => 'The scan failed before a usable result was returned. Check model health or try again when the service is online.'];
        }

        if ($record->recognition_status === 'no_detection') {
            $items[] = ['title' => 'Retake framing', 'detail' => 'Center a single crab in the frame with the whole body visible and minimal background clutter.'];
        }

        if ($record->confidence_level === 'low' || ($record->confidence !== null && $record->confidence < 0.60)) {
            $items[] = ['title' => 'Needs verification', 'detail' => 'This is a low-confidence result. Compare it with reference species or submit feedback for admin review.'];
        } elseif ($record->confidence_level === 'moderate') {
            $items[] = ['title' => 'Review before use', 'detail' => 'The match is plausible, but review visible traits before using it in a report.'];
        } elseif ($record->confidence_level === 'high') {
            $items[] = ['title' => 'Strong visual match', 'detail' => 'The AI returned a strong visual match. Keep food-safety or scientific decisions separate from this confidence score.'];
        }

        foreach (($record->quality_warnings ?? []) as $warning) {
            $items[] = ['title' => 'Image quality', 'detail' => $warning];
        }

        if ($record->brightness_score !== null && $record->brightness_score < 0.25) {
            $items[] = ['title' => 'Lighting', 'detail' => 'Use brighter, even lighting so shell color and markings are easier to detect.'];
        }

        if ($record->species === null && $record->predicted_class !== null) {
            $items[] = ['title' => 'Library gap', 'detail' => 'The AI result is not mapped to a local supported species. An admin can add or map this species.'];
        }

        if ($items === []) {
            $items[] = ['title' => 'Reference check', 'detail' => 'Compare the result against habitat, body shape, claws, and carapace markings before treating it as final.'];
        }

        return array_values(array_unique($items, SORT_REGULAR));
    }
}
