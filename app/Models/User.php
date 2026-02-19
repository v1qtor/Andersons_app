<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $primaryKey = 'userId';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'roleId',
        'name',
        'email',
        'password',
        'iban',
        'phoneNumber',
        'countryId',
        'isActive',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'isActive' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'roleId', 'roleId');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'countryId', 'countryId');
    }

    public function unavailabilityPeriods(): HasMany
    {
        return $this->hasMany(UnavailabilityPeriod::class, 'userId', 'userId');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class, 'userId', 'userId');
    }

    public function allergies(): BelongsToMany
    {
        return $this->belongsToMany(Allergy::class, 'user_allergies', 'userId', 'allergyId');
    }

    public function mealSubscriptions(): BelongsToMany
    {
        return $this->belongsToMany(PlannedMeal::class, 'meal_subscriptions', 'userId', 'plannedMealId')->withPivot('guestName');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'user_tasks', 'userId', 'taskId')->withPivot('isOwner');
    }

    public function notificationSettings(): BelongsToMany
    {
        return $this->belongsToMany(NotificationType::class, 'notification_settings', 'userId', 'notificationTypeId')->withPivot('value');
    }

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'user_trips', 'userId', 'tripId')->withPivot('isOrganizer');
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(Preference::class, 'userId', 'userId');
    }
}
