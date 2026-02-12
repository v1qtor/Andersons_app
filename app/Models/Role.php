<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $primaryKey = 'roleId';

    protected $fillable = [
        'name',
        'color',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'roleId', 'roleId');
    }
}
