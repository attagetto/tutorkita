<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningMaterial extends Model
{
    protected $fillable = [
        'slug', 'title', 'subject', 'topic', 'grade_level', 'tutor_name', 'material_type',
        'description', 'illustration', 'file_path', 'video_url', 'page_count', 'duration_seconds',
        'view_count', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'immutable_datetime',
            'page_count' => 'integer',
            'duration_seconds' => 'integer',
            'view_count' => 'integer',
        ];
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(MaterialBookmark::class);
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(MaterialProgress::class);
    }
}
