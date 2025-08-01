<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Card extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'bank_id',
        'brand_id',
        'name',
        'number',
        'logo',
        'due_date',
        'limit'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function brand()
    {
        return $this->belongsTo(BrandCard::class, 'brand_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function items()
    {
        return $this->hasManyThrough(TransactionItem::class, Transaction::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('family', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('family_id', auth()->user()->family_id);
            }
        });
    }
}
