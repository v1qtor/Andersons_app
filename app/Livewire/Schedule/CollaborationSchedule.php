<?php

namespace App\Livewire\Schedule;

use App\Models\CollaborationRequest;
use Illuminate\Support\Facades\Auth;

trait CollaborationSchedule
{
    public function acceptCollaborationRequest(int $requestId): void
    {
        $request = CollaborationRequest::findOrFail($requestId);

        if ($request->target_user_id !== Auth::id()) {
            return;
        }

        if ($request->status !== 'pending') {
            return;
        }

        $request->update(['status' => 'accepted']);

        $task = $request->task;
        if (! $task->users()->where('users.id', $request->target_user_id)->exists()) {
            $task->users()->attach($request->target_user_id, ['is_owner' => false]);
        }
    }

    public function declineCollaborationRequest(int $requestId): void
    {
        $request = CollaborationRequest::findOrFail($requestId);

        if ($request->target_user_id !== Auth::id()) {
            return;
        }

        if ($request->status !== 'pending') {
            return;
        }

        $request->update(['status' => 'declined']);
    }
}
