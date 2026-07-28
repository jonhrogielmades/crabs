<?php

namespace App\Services;

class RecognitionReferenceService
{
    public static function generate(): string
    {
        return 'CRAB-'.now()->format('Ymd').'-'.strtoupper(str()->random(8));
    }

    public static function confidenceLevel(?float $confidence): string
    {
        if ($confidence === null) return 'unknown';
        $minimum = (float) config('services.ai.confidence_threshold', 0.60);
        $high = (float) config('services.ai.high_confidence_threshold', 0.85);
        if ($confidence < $minimum) return 'low';
        return $confidence >= $high ? 'high' : 'moderate';
    }
}
