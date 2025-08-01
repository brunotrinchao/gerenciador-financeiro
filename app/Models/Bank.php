<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Bank extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'code'];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
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
            $model->family_id ??= auth()->user()->family_id;
        });
    }
}
