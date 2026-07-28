<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecognitionFeedback extends Model
{
    protected $table = 'recognition_feedback';
    protected $fillable = ['recognition_record_id', 'user_id', 'category', 'description', 'status', 'admin_response', 'resolved_by', 'resolved_at'];
    protected function casts(): array { return ['resolved_at' => 'datetime']; }
    public function recognitionRecord(): BelongsTo { return $this->belongsTo(RecognitionRecord::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function resolver(): BelongsTo { return $this->belongsTo(User::class, 'resolved_by'); }
}
