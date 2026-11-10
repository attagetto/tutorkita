<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GradeLevel extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)->withTimestamps();
    }
}
