<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'user_id',
        'amount',
        'bill_date',
        'description',
        'is_paid',
        'file_path',
        'upload_date',
        'paid_date',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'bill_date' => 'datetime',
            'is_paid' => 'boolean',
            'upload_date' => 'datetime',
            'paid_date' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
