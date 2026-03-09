<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'repeatability_type_id',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'end_date' => 'datetime',
        ];
    }

    public function repeatabilityType(): BelongsTo
    {
        return $this->belongsTo(RepeatabilityType::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
