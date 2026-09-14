<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'notes',
        'start_date',
        'end_date',
        'trip_category_id',
        'buffer_alert',
        'status_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'buffer_alert' => 'datetime',
        ];
    }

    public function tripCategory(): BelongsTo
    {
        return $this->belongsTo(TripCategory::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function attachedFiles(): HasMany
    {
        return $this->hasMany(AttachedFile::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_trips')
            ->withPivot('is_organizer')
            ->withTimestamps();
    }

    public function checkpoints(): BelongsToMany
    {
        return $this->belongsToMany(Checkpoint::class, 'trip_checkpoints')
            ->withPivot('arrival_date', 'is_confirmed', 'order', 'is_temporary', 'image_path', 'temp_location', 'temp_address')
            ->withTimestamps()
            ->orderByPivot('order');
    }

    public function checkpointImages(): HasMany
    {
        return $this->hasMany(CheckpointImage::class);
    }

    public function plusOnes(): HasMany
    {
        return $this->hasMany(PlusOne::class);
    }

    /**
     * Resolve the correct trip status (upcoming/active/completed) for a given date range.
     */
    public static function resolveStatusFor($start, $end): ?Status
    {
        $now = now();
        $start = \Carbon\Carbon::parse($start);
        $end = \Carbon\Carbon::parse($end);

        $name = match (true) {
            $now->lt($start) => 'upcoming',
            $now->lte($end) => 'active',
            default => 'completed',
        };

        return Status::where('name', $name)->where('type', 'trip')->first();
    }

    public function getIsOverdueAttribute(): bool
    {
        if (! $this->buffer_alert || now()->lte($this->buffer_alert)) {
            return false;
        }

        return ! in_array($this->status?->name, ['completed', 'cancelled'], true);
    }
}