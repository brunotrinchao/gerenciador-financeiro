<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;


class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'card_id',
        'category_id',
        'type',
        'amount',
        'date',
        'method',
        'is_recurring',
        'recurrence_interval',
        'description',
        'family_id'
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
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
    }
}
