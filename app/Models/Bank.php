<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = ['name', 'code'];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }
}
