<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class Transfer extends Model
{
    protected $fillable = ['source_transaction_id', 'target_transaction_id'];

    public function source()
    {
        return $this->belongsTo(Transaction::class, 'source_transaction_id');
    }

    public function target()
    {
        return $this->belongsTo(Transaction::class, 'target_transaction_id');
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }
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
