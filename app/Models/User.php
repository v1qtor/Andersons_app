<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'iban',
        'phone_number',
        'address',
        'is_active',
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
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'initials',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    protected function initials(): Attribute
    {
        return Attribute::get(fn () => Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode(''));
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function unavailabilityPeriods(): HasMany
    {
    return $this->hasMany(UnavailabilityPeriod::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function allergies(): BelongsToMany
    {
        return $this->belongsToMany(Allergy::class, 'user_allergies');
    }

    public function mealSubscriptions(): BelongsToMany
    {
        return $this->belongsToMany(PlannedMeal::class, 'meal_subscriptions')->withPivot('guest_name');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'user_tasks')->withPivot('is_owner');
    }

    public function notificationSettings(): BelongsToMany
    {
        return $this->belongsToMany(NotificationType::class, 'notification_settings')->withPivot('value');
    }

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'user_trips')->withPivot('is_organizer');
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(Preference::class);
    }

    public function sentCollaborationRequests(): HasMany
    {
        return $this->hasMany(CollaborationRequest::class, 'requester_id');
    }

    public function receivedCollaborationRequests(): HasMany
    {
        return $this->hasMany(CollaborationRequest::class, 'target_user_id');
    }
}
