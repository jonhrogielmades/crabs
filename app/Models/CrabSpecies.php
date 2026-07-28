<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrabSpecies extends Model
{
    use SoftDeletes;

    protected $fillable = ['common_name', 'scientific_name', 'local_name', 'family', 'classification', 'habitat', 'description', 'visual_characteristics', 'edible_status', 'caution_notes', 'reference_image_path', 'reference_name', 'reference_url', 'image_credit', 'model_class_name', 'model_class_id', 'is_supported', 'is_active'];

    protected function casts(): array
    {
        return ['is_supported' => 'boolean', 'is_active' => 'boolean'];
    }

    public function recognitionRecords(): HasMany
    {
        return $this->hasMany(RecognitionRecord::class);
    }
}
