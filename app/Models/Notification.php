<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications_custom';

    protected $primaryKey = 'notificationId';

    protected $fillable = [
        'title',
        'description',
        'isMail',
    ];

    protected function casts(): array
    {
        return [
            'isMail' => 'boolean',
        ];
    }
}
