<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['name', 'slug', 'category', 'description', 'icon', 'tone'];

    public function gradeLevels(): BelongsToMany
    {
        return $this->belongsToMany(GradeLevel::class)->orderBy('sort_order');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_subjects', 'subject_id', 'student_id', 'id', 'id')
            ->withPivot('grade_level_id')
            ->withTimestamps();
    }

    /** Materials still store subject names as text, so relate them through that key. */
    public function materials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'subject', 'name');
    }
}
