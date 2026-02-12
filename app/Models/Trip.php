<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Trip extends Model
{
    use HasFactory;

    protected $primaryKey = 'tripId';

    protected $fillable = [
        'name',
        'description',
        'startDate',
        'endDate',
        'tripCategoryId',
        'bufferAlert',
        'statusId',
        'attachedFileId',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'startDate' => 'datetime',
            'endDate' => 'datetime',
            'bufferAlert' => 'datetime',
        ];
    }

    public function tripCategory(): BelongsTo
    {
        return $this->belongsTo(TripCategory::class, 'tripCategoryId', 'tripCategoryId');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'statusId', 'statusId');
    }

    public function attachedFile(): BelongsTo
    {
        return $this->belongsTo(AttachedFile::class, 'attachedFileId', 'attachedFileId');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_trips', 'tripId', 'userId')->withPivot('isOrganizer');
    }

    public function checkpoints(): BelongsToMany
    {
        return $this->belongsToMany(Checkpoint::class, 'trip_checkpoints', 'tripId', 'checkpointId')->withPivot('arrivalDate', 'isConfirmed', 'order');
    }
}
