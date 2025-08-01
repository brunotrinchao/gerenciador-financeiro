<?php

namespace App\Models;

use App\Observers\TransactionItemObserver;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(TransactionItemObserver::class)]
class TransactionItem extends Model
{
    use LogsActivity;
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'payment_date',
        'due_date',
        'amount',
        'status',
        'installment_number',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('family', function (Builder $builder) {
            if (auth()->check()) {
                $builder->whereHas('transaction', function ($builder) {
                    $builder->where('family_id', auth()->user()->family_id);
                });
            }
        });
    }
}
