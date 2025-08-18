<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'family_id'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('family', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where(function ($query) {
                    $query->whereNull('family_id')
                        ->orWhere('family_id', auth()->user()->family_id);
                });
            }
        });

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->family_id ??= auth()->user()->family_id;
            }
        });
    }
}
