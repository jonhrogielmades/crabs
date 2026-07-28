<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['setting_key', 'setting_value', 'value_type', 'group', 'is_public'];
    protected function casts(): array { return ['is_public' => 'boolean']; }
}
