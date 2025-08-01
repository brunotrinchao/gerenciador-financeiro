<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Family extends Model
{

    use HasFactory;
    use SoftDeletes;

    protected $table = 'family';

    protected $fillable = ['name', 'type', 'status'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
