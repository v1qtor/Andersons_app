<?php

namespace Database\Factories;

use App\Models\CollaborationRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollaborationRequest>
 */
class CollaborationRequestFactory extends Factory
{
    protected $model = CollaborationRequest::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'requester_id' => User::factory(),
            'target_user_id' => User::factory(),
            'status' => 'pending',
        ];
    }
}
