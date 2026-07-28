<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ImageQualityService
{
    public function assess(UploadedFile $image): array
    {
        $warnings = [];
        $info = @getimagesize($image->getRealPath());
        if (! $info) {
            return ['acceptable' => false, 'warnings' => ['The image file appears to be corrupt or unsupported.']];
        }

        [$width, $height] = $info;
        if ($width < 320 || $height < 240) $warnings[] = 'The image resolution is too low. Move closer and retake it.';

        return ['acceptable' => count($warnings) === 0, 'warnings' => $warnings, 'blur_score' => null, 'brightness_score' => null];
    }
}
