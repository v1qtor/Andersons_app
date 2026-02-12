<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;

    protected $primaryKey = 'statusId';

    protected $fillable = [
        'name',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'statusId', 'statusId');
    }
}
