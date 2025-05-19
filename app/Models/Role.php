<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    // Defina os atributos que podem ser preenchidos em massa (mass assignment)
    protected $fillable = ['name', 'guard_name'];

    // Defina a tabela associada à model (se não for o plural da model)
    protected $table = 'roles';

    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    // Relacionamento entre Role e User
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'model_has_roles');
    }


}
