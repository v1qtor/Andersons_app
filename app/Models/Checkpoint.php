<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Checkpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'description',
        'address',
        'latitude',
        'longitude',
        'coordinates',
        'folder_id',
        'user_id',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_checkpoints')
            ->withPivot('arrival_date', 'is_confirmed', 'order', 'is_temporary', 'temp_location', 'temp_address', 'image_path')
            ->withTimestamps();
    }
}