<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $primaryKey = 'taskId';

    protected $fillable = [
        'title',
        'description',
        'startDate',
        'endDate',
        'taskCategoryId',
        'taskPriorityId',
        'isComplete',
        'date',
        'recurringTaskId',
    ];

    protected function casts(): array
    {
        return [
            'startDate' => 'datetime',
            'endDate' => 'datetime',
            'date' => 'datetime',
            'isComplete' => 'boolean',
        ];
    }

    public function taskCategory(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'taskCategoryId', 'taskCategoryId');
    }

    public function taskPriority(): BelongsTo
    {
        return $this->belongsTo(TaskPriority::class, 'taskPriorityId', 'taskPriorityId');
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTask::class, 'recurringTaskId', 'recurringTaskId');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_tasks', 'taskId', 'userId')->withPivot('isOwner');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'task_locations', 'taskId', 'locationId');
    }
}
