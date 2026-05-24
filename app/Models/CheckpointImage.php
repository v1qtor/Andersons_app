<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckpointImage extends Model
{
    protected $fillable = ['trip_id', 'checkpoint_id', 'image_path', 'uploaded_by'];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(Checkpoint::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
