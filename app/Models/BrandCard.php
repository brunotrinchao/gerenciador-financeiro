<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandCard extends Model
{
    use HasFactory;
    protected $table = 'brand_cards';
    protected $fillable = [
        'name',
        'slug',
        'brand',
    ];

    public function cards()
    {
        return $this->hasMany(Card::class, 'brand_id');
    }
}
