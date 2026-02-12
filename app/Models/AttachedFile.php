<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttachedFile extends Model
{
    use HasFactory;

    protected $primaryKey = 'attachedFileId';

    protected $fillable = [
        'filePath',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'attachedFileId', 'attachedFileId');
    }
}
