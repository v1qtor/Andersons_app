<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlannedMeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_id',
        'date_time',
        'notes',
        'is_prepared',
    ];

    protected function casts(): array
    {
        return [
            'date_time'   => 'datetime',
            'is_prepared' => 'boolean',
        ];
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meal_subscriptions')->withPivot('confirmed')->withTimestamps();
    }

    public function guests(): HasMany
    {
        return $this->hasMany(MealGuest::class);
    }
}
