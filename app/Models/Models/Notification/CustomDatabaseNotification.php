<?php

namespace App\Models\Models\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Builder;

class CustomDatabaseNotification extends DatabaseNotification
{
    protected static function booted(): void
    {
        static::creating(function ($notification) {
            if (auth()->check()) {
                $notification->family_id = auth()->user()->family_id;
            }
        });

        static::addGlobalScope('family', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('family_id', auth()->user()->family_id);
            }
        });
    }
}
