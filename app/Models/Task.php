<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'task_category_id',
        'task_priority_id',
        'is_complete',
        'date',
        'recurring_task_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'date' => 'datetime',
            'is_complete' => 'boolean',
        ];
    }

    public function taskCategory(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class);
    }

    public function taskPriority(): BelongsTo
    {
        return $this->belongsTo(TaskPriority::class);
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTask::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tasks')->withPivot('is_owner');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'task_locations');
    }

    public function collaborationRequests(): HasMany
    {
        return $this->hasMany(CollaborationRequest::class);
    }
}
