<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class ActionLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope('family', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('family_id', auth()->user()->family_id);
            }
        });

        static::creating(function ($model) {
            $model->family_id ??= auth()->user()->family_id;
        });
    }



}
