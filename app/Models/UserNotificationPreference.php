<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationPreference extends Model
{
    protected $fillable = ['user_id', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
