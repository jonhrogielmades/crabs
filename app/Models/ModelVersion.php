<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelVersion extends Model
{
    protected $fillable = ['name', 'version', 'description', 'classes', 'confidence_threshold', 'evaluation_metrics', 'deployed_at', 'is_active'];
    protected function casts(): array { return ['classes' => 'array', 'evaluation_metrics' => 'array', 'deployed_at' => 'datetime', 'is_active' => 'boolean', 'confidence_threshold' => 'float']; }
}
