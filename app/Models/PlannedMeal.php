<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlannedMeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_id',
        'date_time',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_time' => 'datetime',
        ];
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meal_subscriptions')->withPivot('guest_name');
    }
}
