<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = ['user_id', 'notification_key', 'category', 'title', 'body', 'action_url', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'immutable_datetime'];
    }
}
