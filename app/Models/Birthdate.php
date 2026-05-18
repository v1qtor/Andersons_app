<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Birthdate extends Model
{
    protected $fillable = [
        'name',
        'birthdate',
        'user_id',
        'is_user',
        'notes',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'is_user'   => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
