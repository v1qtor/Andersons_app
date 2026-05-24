<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlusOne extends Model
{
    use HasFactory;

    protected $fillable = ['trip_id', 'added_by', 'name', 'email', 'phone'];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
