<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringTask extends Model
{
    use HasFactory;

    protected $primaryKey = 'recurringTaskId';

    protected $fillable = [
        'repeatabilityTypeId',
        'endDate',
    ];

    protected function casts(): array
    {
        return [
            'endDate' => 'datetime',
        ];
    }

    public function repeatabilityType(): BelongsTo
    {
        return $this->belongsTo(RepeatabilityType::class, 'repeatabilityTypeId', 'repeatabilityTypeId');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'recurringTaskId', 'recurringTaskId');
    }
}
