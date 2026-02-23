<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications_custom';

    protected $fillable = [
        'title',
        'description',
        'is_mail',
    ];

    protected function casts(): array
    {
        return [
            'is_mail' => 'boolean',
        ];
    }
}
