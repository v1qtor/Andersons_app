<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'trip_category_id',
        'buffer_alert',
        'status_id',
        'attached_file_id',
        'notes',
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

    public function attachedFile(): BelongsTo
    {
        return $this->belongsTo(AttachedFile::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_trips')->withPivot('is_organizer');
    }

    public function checkpoints(): BelongsToMany
    {
        return $this->belongsToMany(Checkpoint::class, 'trip_checkpoints')->withPivot('arrival_date', 'is_confirmed', 'order');
    }
}
