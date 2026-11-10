<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorApplicationReview extends Model
{
    protected $fillable = ['tutor_application_id', 'actor_id', 'action', 'from_status', 'to_status', 'reason'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(TutorApplication::class, 'tutor_application_id');
    }
}
