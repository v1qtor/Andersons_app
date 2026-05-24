<?php

namespace App\Livewire\Schedule;

use App\Models\CollaborationRequest;
use App\Models\Task;
use App\Models\UnavailabilityPeriod;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait CrudSchedule
{
    // ─── Task Form Properties ─────────────────────────────────
    public bool $showTaskModal = false;
    public ?int $editingTaskId = null;
    public string $title = '';
    public string $description = '';
    public string $startDate = '';
    public ?string $endDate = '';
    public ?int $taskCategoryId = null;
    public ?int $taskPriorityId = null;
    public bool $isComplete = false;
    public ?int $taskOwnerId = null;
    public array $assignedUserIds = [];
    public array $selectedLocationIds = [];
    public array $collaborationUserIds = [];

    public bool $showDeleteModal = false;
    public ?int $deletingTaskId = null;

    // ─── Modal Open/Close ─────────────────────────────────────

    public function openCreateModal(?string $date = null): void
    {
        $this->resetForm();
        $this->taskOwnerId = Auth::id();
        $this->assignedUserIds = [Auth::id()];
        if ($date) {
            $this->startDate = $date . 'T09:00';
            $this->endDate   = $date . 'T10:00';
        }
        $this->showTaskModal = true;
    }

    public function openEditModal(int $taskId): void
    {
        $task = Task::findOrFail($taskId);

        if (! $this->canManageTask($task)) {
            return;
        }

        $this->editingTaskId        = $task->id;
        $this->title                = $task->title;
        $this->description          = $task->description ?? '';
        $this->startDate            = $task->start_date->format('Y-m-d\TH:i');
        $this->endDate              = $task->end_date ? $task->end_date->format('Y-m-d\TH:i') : '';
        $this->taskCategoryId       = $task->task_category_id;
        $this->taskPriorityId       = $task->task_priority_id;
        $this->isComplete           = $task->is_complete;
        $this->taskOwnerId          = $task->users()->wherePivot('is_owner', true)->value('users.id');
        $this->assignedUserIds      = $task->users->pluck('id')->toArray();
        $this->selectedLocationIds  = $task->locations->pluck('id')->toArray();
        $this->showTaskModal        = true;
    }

    // ─── Field Toggles ────────────────────────────────────────

    public function setTaskOwner(int $userId): void
    {
        if (! $this->isAdmin()) {
            return;
        }
        $this->taskOwnerId = $userId;
    }

    public function toggleAssignedUser(int $userId): void
    {
        if (in_array($userId, $this->assignedUserIds)) {
            $this->assignedUserIds = array_values(array_diff($this->assignedUserIds, [$userId]));
        } else {
            $this->assignedUserIds[] = $userId;
        }
    }

    public function toggleLocation(int $locationId): void
    {
        if (in_array($locationId, $this->selectedLocationIds)) {
            $this->selectedLocationIds = array_values(array_diff($this->selectedLocationIds, [$locationId]));
        } else {
            $this->selectedLocationIds[] = $locationId;
        }
    }

    public function toggleCollaborationUser(int $userId): void
    {
        if (in_array($userId, $this->collaborationUserIds)) {
            $this->collaborationUserIds = array_values(array_diff($this->collaborationUserIds, [$userId]));
        } else {
            $this->collaborationUserIds[] = $userId;
        }
    }

    // ─── Save ─────────────────────────────────────────────────

    public function saveTask(): void
    {
        $endDateValue = ($this->endDate !== null && $this->endDate !== '') ? $this->endDate : null;

        $validationData = [
            'title'          => $this->title,
            'description'    => $this->description !== '' ? $this->description : null,
            'startDate'      => $this->startDate,
            'endDate'        => $endDateValue,
            'taskCategoryId' => $this->taskCategoryId,
            'taskPriorityId' => $this->taskPriorityId,
        ];

        $startDateRule = 'required|date';
        if (! $this->isAdmin() && ! $this->editingTaskId) {
            $startDateRule .= '|after_or_equal:now';
        }

        $validated = \Illuminate\Support\Facades\Validator::make($validationData, [
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'startDate'      => $startDateRule,
            'endDate'        => 'nullable|date|after:startDate',
            'taskCategoryId' => 'required|integer|exists:task_categories,id',
            'taskPriorityId' => 'nullable|integer|exists:task_priorities,id',
        ], [
            'startDate.after_or_equal' => __('You cannot create a task in the past.'),
        ], [
            'taskCategoryId' => __('category'),
            'taskPriorityId' => __('priority'),
            'startDate'      => __('start date'),
            'endDate'        => __('end date'),
        ])->validate();

        $startDt = Carbon::parse($validated['startDate']);
        $endDt   = $validated['endDate'] ? Carbon::parse($validated['endDate']) : null;

        $data = [
            'title'            => $validated['title'],
            'description'      => $validated['description'],
            'start_date'       => $startDt,
            'end_date'         => $endDt,
            'date'             => $startDt,
            'task_category_id' => $validated['taskCategoryId'],
            'task_priority_id' => $validated['taskPriorityId'],
            'is_complete'      => $this->editingTaskId ? $this->isComplete : false,
        ];

        // Check that none of the users being assigned are unavailable during this period
        $allUserIdsToAssign = array_unique(array_filter(array_merge(
            $this->isAdmin() ? $this->assignedUserIds : [],
            $this->taskOwnerId ? [$this->taskOwnerId] : [],
        )));

        if (! empty($allUserIdsToAssign)) {
            $unavailableIds = UnavailabilityPeriod::whereIn('user_id', $allUserIdsToAssign)
                ->where('start_date', '<', $endDt ?? $startDt->copy()->addHour())
                ->where('end_date', '>', $startDt)
                ->pluck('user_id')
                ->unique()
                ->toArray();

            if (! empty($unavailableIds)) {
                \Illuminate\Support\Facades\Validator::make([], [])->errors();
                $this->addError('startDate', __('One or more selected users are unavailable during this period.'));
                return;
            }
        }

        if ($this->editingTaskId) {
            $task = Task::findOrFail($this->editingTaskId);
            if (! $this->canManageTask($task)) {
                return;
            }
            $task->update($data);
            $task->locations()->sync($this->selectedLocationIds);

            if ($this->isAdmin()) {
                $previousUserIds = $task->users()->pluck('users.id')->toArray();
                $ownerId   = $this->taskOwnerId
                    ?: $task->users()->wherePivot('is_owner', true)->value('users.id');
                $syncData  = [];
                if ($ownerId) {
                    $syncData[$ownerId] = ['is_owner' => true];
                }
                foreach ($this->assignedUserIds as $uid) {
                    if (! isset($syncData[$uid])) {
                        $syncData[$uid] = ['is_owner' => false];
                    }
                }

                $newUserIds = array_keys($syncData);
                $addedUserIds = array_diff($newUserIds, $previousUserIds);
                $task->users()->sync($syncData);

                if (! empty($addedUserIds)) {
                    $taskDateTime = $task->start_date
                        ? $task->start_date->format('M d, H:i')
                        : 'No date set';

                    foreach ($addedUserIds as $userId) {
                        if ($userId === (int) Auth::id()) {
                            continue;
                        }

                        $notification = UserNotification::create([
                            'user_id' => $userId,
                            'from_user_id' => Auth::id(),
                            'title' => 'Task Assigned',
                            'message' => Auth::user()->name . ' assigned you a task: ' . $task->title . ' on ' . $taskDateTime,
                            'type' => 'task_assigned',
                            'action_url' => '/schedule',
                        ]);

                        broadcast(new \App\Events\NotificationCreated($notification));
                    }
                }
            }
        } else {
            $task    = Task::create($data);
            $ownerId = ($this->isAdmin() && $this->taskOwnerId)
                ? $this->taskOwnerId
                : Auth::id();

            $syncData               = [];
            $syncData[$ownerId]     = ['is_owner' => true];

            if ($this->isAdmin()) {
                foreach ($this->assignedUserIds as $uid) {
                    if (! isset($syncData[$uid])) {
                        $syncData[$uid] = ['is_owner' => false];
                    }
                }
            }

            $task->users()->sync($syncData);
            $task->locations()->sync($this->selectedLocationIds);

            if ($this->isAdmin() && ! empty($this->assignedUserIds)) {
                $taskDateTime = $task->start_date
                    ? $task->start_date->format('M d, H:i')
                    : 'No date set';

                foreach ($this->assignedUserIds as $userId) {
                    if ($userId === $ownerId || $userId === (int) Auth::id()) {
                        continue;
                    }

                    $notification = UserNotification::create([
                        'user_id' => $userId,
                        'from_user_id' => Auth::id(),
                        'title' => 'Task Assigned',
                        'message' => Auth::user()->name . ' assigned you a task: ' . $task->title . ' on ' . $taskDateTime,
                        'type' => 'task_assigned',
                        'action_url' => '/schedule',
                    ]);

                    broadcast(new \App\Events\NotificationCreated($notification));
                }
            }
        }

        // Non-admin: send collaboration requests
        if (! $this->isAdmin() && ! empty($this->collaborationUserIds)) {
            foreach ($this->collaborationUserIds as $targetUserId) {
                if ($targetUserId === Auth::id()) {
                    continue;
                }
                $exists = CollaborationRequest::where('task_id', $task->id)
                    ->where('target_user_id', $targetUserId)
                    ->where('status', 'pending')
                    ->exists();

                if (! $exists) {
                    CollaborationRequest::create([
                        'task_id'        => $task->id,
                        'requester_id'   => Auth::id(),
                        'target_user_id' => $targetUserId,
                        'status'         => 'pending',
                    ]);

                    $taskDateTime = $task->start_date
                        ? $task->start_date->format('M d, H:i')
                        : 'No date set';

                    $notification = UserNotification::create([
                        'user_id' => $targetUserId,
                        'from_user_id' => Auth::id(),
                        'title' => 'Collaboration Request',
                        'message' => Auth::user()->name . ' is requesting your collaboration on: ' . $task->title . ' on ' . $taskDateTime,
                        'type' => 'collaboration_request',
                        'action_url' => '/schedule',
                    ]);

                    broadcast(new \App\Events\NotificationCreated($notification));
                }
            }
        }

        $this->showTaskModal = false;
        $this->resetForm();
    }

    // ─── Delete ───────────────────────────────────────────────

    public function confirmDelete(int $taskId): void
    {
        $this->deletingTaskId = $taskId;
        $this->showDeleteModal = true;
    }

    public function deleteTask(): void
    {
        if (! $this->deletingTaskId) {
            return;
        }

        $task = Task::findOrFail($this->deletingTaskId);

        if (! $this->canManageTask($task)) {
            return;
        }

        $task->users()->detach();
        $task->locations()->detach();
        $task->delete();

        $this->showDeleteModal = false;
        $this->deletingTaskId  = null;
    }

    // ─── Mark Complete ────────────────────────────────────────

    public function markComplete(int $taskId): void
    {
        $task       = Task::findOrFail($taskId);
        $isAssigned = $task->users()->where('users.id', Auth::id())->exists();

        if (! $isAssigned && ! $this->isAdmin()) {
            return;
        }

        $task->update(['is_complete' => true]);
    }

    // ─── Form Reset ───────────────────────────────────────────

    private function resetForm(): void
    {
        $this->editingTaskId        = null;
        $this->title                = '';
        $this->description          = '';
        $this->startDate            = '';
        $this->endDate              = '';
        $this->taskCategoryId       = null;
        $this->taskPriorityId       = null;
        $this->isComplete           = false;
        $this->taskOwnerId          = null;
        $this->assignedUserIds      = [];
        $this->selectedLocationIds  = [];
        $this->collaborationUserIds = [];
    }
}
