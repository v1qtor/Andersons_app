<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Checkpoint extends Model
{
    use HasFactory;

    protected $primaryKey = 'checkpointId';

    protected $fillable = [
        'location',
        'address',
        'coordinates',
        'folderId',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folderId', 'folderId');
    }

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_checkpoints', 'checkpointId', 'tripId')->withPivot('arrivalDate', 'isConfirmed', 'order');
    }
}
