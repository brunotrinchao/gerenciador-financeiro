<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
