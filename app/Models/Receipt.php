<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    use HasFactory;

    protected $primaryKey = 'receiptId';

    protected $fillable = [
        'categoryId',
        'userId',
        'amount',
        'billDate',
        'description',
        'isPaid',
        'filePath',
        'uploadDate',
        'paidDate',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'billDate' => 'datetime',
            'isPaid' => 'boolean',
            'uploadDate' => 'datetime',
            'paidDate' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categoryId', 'categoryId');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }
}
