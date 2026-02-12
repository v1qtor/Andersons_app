<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlannedMeal extends Model
{
    use HasFactory;

    protected $primaryKey = 'plannedMealId';

    protected $fillable = [
        'mealId',
        'dateTime',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'dateTime' => 'datetime',
        ];
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class, 'mealId', 'mealId');
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meal_subscriptions', 'plannedMealId', 'userId')->withPivot('guestName');
    }
}
