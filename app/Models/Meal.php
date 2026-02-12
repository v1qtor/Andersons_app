<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meal extends Model
{
    use HasFactory;

    protected $primaryKey = 'mealId';

    protected $fillable = [
        'name',
        'description',
    ];

    public function plannedMeals(): HasMany
    {
        return $this->hasMany(PlannedMeal::class, 'mealId', 'mealId');
    }
}
