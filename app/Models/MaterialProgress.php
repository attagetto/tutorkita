<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialProgress extends Model
{
    protected $fillable = ['learning_material_id', 'student_id', 'last_opened_at', 'completed_at'];

    protected function casts(): array
    {
        return ['last_opened_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime'];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(LearningMaterial::class, 'learning_material_id');
    }
}
