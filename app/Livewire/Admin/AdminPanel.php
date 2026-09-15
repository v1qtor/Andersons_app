<?php

namespace App\Livewire\Admin;

use App\Models\Birthdate;
use App\Models\Category;
use App\Models\Location;
use App\Models\Role;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class AdminPanel extends Component
{
    use WithPagination;

    // Tab management
    public string $activeTab = 'users';

    // Search (per tab)
    public array $search = [];

    // Inline editing state
    public ?int $editingId = null;

    public string $editingValue = '';

    public bool $creatingNew = false;

    public string $newEntityName = '';

    // Color editing state
    public string $editingColor = '';

    public ?int $editingColorRoleId = null;

    // Password confirmation modal state
    public bool $showConfirmModal = false;

    public string $pendingAction = '';

    public int $pendingUserId = 0;

    public int $pendingEntityId = 0;

    public string $confirmPassword = '';

    public string $passwordError = '';

    // Birthdate management
    public bool $showBirthdateModal = false;

    public ?int $editingBirthdateId = null;

    public string $bdName = '';

    public string $bdDate = '';

    public ?int $bdUserId = null;

    public string $bdNotes = '';

    public bool $showDeleteBirthdateConfirm = false;

    public ?int $confirmingDeleteBirthdateId = null;

    // Tab management
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // User management methods (copied from UserIndex)
    public function prepareAction(string $action, int $userId): void
    {
        $this->pendingAction = $action;
        $this->pendingUserId = $userId;
        $this->confirmPassword = '';
        $this->passwordError = '';
        $this->showConfirmModal = true;
    }

    public function executeAction(): void
    {
        // Delegate to executeDelete which handles all action types
        $this->executeDelete();
    }

    public function cancelAction(): void
    {
        $this->showConfirmModal = false;
        $this->pendingAction = '';
        $this->pendingUserId = 0;
        $this->pendingEntityId = 0;
        $this->confirmPassword = '';
        $this->passwordError = '';
        $this->editingColorRoleId = null;
        $this->editingColor = '';
        session()->forget('pending_entity_type');
    }

    private function redirectToEdit(int $userId): void
    {
        $this->redirect(route('admin.users.edit', $userId), navigate: true);
    }

    public function deleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole && $user->role_id === $adminRole->id) {
            $adminCount = User::where('role_id', $adminRole->id)->count();
            if ($adminCount <= 1) {
                $this->dispatch('toast', message: __('Cannot delete the last admin user.'), type: 'error');

                return;
            }
        }

        $user->delete();

        $this->dispatch('toast', message: __('User deleted successfully.'), type: 'success');
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->is_active) {
            $adminRole = Role::where('name', 'Admin')->first();
            if ($adminRole && $user->role_id === $adminRole->id) {
                $activeAdminCount = User::where('role_id', $adminRole->id)->where('is_active', true)->count();
                if ($activeAdminCount <= 1) {
                    $this->dispatch('toast', message: __('Cannot deactivate the last active admin user.'), type: 'error');

                    return;
                }
            }
        }

        $user->update(['is_active' => ! $user->is_active]);

        $message = $user->is_active ? __('User activated successfully.') : __('User deactivated successfully.');
        $this->dispatch('toast', message: $message, type: 'success');
    }

    // Inline creation methods
    public function startCreating(): void
    {
        $this->creatingNew = true;
        $this->newEntityName = '';
    }

    public function saveNew(string $entityType): void
    {
        // Validate entity name
        $this->validate([
            'newEntityName' => 'required|max:255',
        ], [
            'newEntityName.required' => 'Name is required',
            'newEntityName.max' => 'Name is too long',
        ]);

        $modelClass = $this->getEntityModel($entityType);

        // Check for duplicate names
        $exists = $modelClass::where('name', $this->newEntityName)->exists();
        if ($exists) {
            $this->dispatch('toast', message: __('Name already exists.'), type: 'error');

            return;
        }

        // Show password confirmation modal before creating
        $this->pendingAction = 'create_entity';
        $this->confirmPassword = '';
        $this->passwordError = '';
        $this->showConfirmModal = true;

        // Store entity type in session
        session()->put('pending_entity_type', $entityType);
    }

    public function cancelCreating(): void
    {
        $this->creatingNew = false;
        $this->newEntityName = '';
    }

    // Inline editing methods
    public function startEditing(int $id, string $currentValue): void
    {
        $this->editingId = $id;
        $this->editingValue = $currentValue;
    }

    public function saveEdit(string $entityType, int $id): void
    {
        // Validate entity name
        $this->validate([
            'editingValue' => 'required|max:255',
        ], [
            'editingValue.required' => 'Name is required',
            'editingValue.max' => 'Name is too long',
        ]);

        $modelClass = $this->getEntityModel($entityType);

        // Check for duplicate names (excluding current entity)
        $exists = $modelClass::where('name', $this->editingValue)
            ->where('id', '!=', $id)
            ->exists();
        if ($exists) {
            $this->dispatch('toast', message: __('Name already exists.'), type: 'error');

            return;
        }

        // Show password confirmation modal before updating
        $this->pendingAction = 'edit_entity';
        $this->pendingEntityId = $id;
        $this->confirmPassword = '';
        $this->passwordError = '';
        $this->showConfirmModal = true;

        // Store entity type in session
        session()->put('pending_entity_type', $entityType);
    }

    public function cancelEditing(): void
    {
        $this->editingId = null;
        $this->editingValue = '';
    }

    // Entity deletion with relationship validation
    public function prepareDelete(string $entityType, int $id): void
    {
        // Validate relationships before showing password modal
        $error = $this->validateRelationships($entityType, $id);

        if ($error) {
            // If relationships exist, display error toast message with count
            $this->dispatch('toast', message: __($error), type: 'error');

            return;
        }

        // If no relationships, show password confirmation modal
        $this->pendingAction = 'delete_entity';
        $this->pendingEntityId = $id;
        $this->confirmPassword = '';
        $this->passwordError = '';
        $this->showConfirmModal = true;

        // Store entity type in session for executeDelete
        session()->put('pending_entity_type', $entityType);
    }

    public function executeDelete(): void
    {
        // Verify password
        if (! Hash::check($this->confirmPassword, Auth::user()->password)) {
            $this->passwordError = __('Incorrect password.');

            return;
        }

        // Handle different action types
        if ($this->pendingAction === 'create_entity') {
            $this->executeCreateEntity();
        } elseif ($this->pendingAction === 'edit_entity') {
            $this->executeEditEntity();
        } elseif ($this->pendingAction === 'delete_entity') {
            $this->deleteEntity();
        } elseif ($this->pendingAction === 'delete') {
            $this->deleteUser($this->pendingUserId);
        } elseif ($this->pendingAction === 'toggle') {
            $this->toggleActive($this->pendingUserId);
        } elseif ($this->pendingAction === 'edit') {
            $this->redirectToEdit($this->pendingUserId);
        } elseif ($this->pendingAction === 'save_color') {
            $this->executeSaveColor();
        }

        $this->cancelAction();
    }

    private function executeCreateEntity(): void
    {
        $entityType = session()->get('pending_entity_type');

        if (! $entityType) {
            $this->dispatch('toast', message: __('Invalid entity type.'), type: 'error');

            return;
        }

        $modelClass = $this->getEntityModel($entityType);

        // Create new entity record
        $modelClass::create([
            'name' => $this->newEntityName,
        ]);

        // Display success toast message
        $entityLabel = str_replace('_', ' ', $entityType);
        // Remove trailing 's' or 'ies' and make singular
        if (str_ends_with($entityLabel, 'ies')) {
            $entityLabel = substr($entityLabel, 0, -3).'y';
        } else {
            $entityLabel = rtrim($entityLabel, 's');
        }
        $entityLabel = ucfirst($entityLabel); // Capitalize
        $this->dispatch('toast', message: __($entityLabel.' created successfully.'), type: 'success');

        // Reset creation state
        $this->creatingNew = false;
        $this->newEntityName = '';

        // Clean up session
        session()->forget('pending_entity_type');
    }

    private function executeEditEntity(): void
    {
        $entityType = session()->get('pending_entity_type');

        if (! $entityType) {
            $this->dispatch('toast', message: __('Invalid entity type.'), type: 'error');

            return;
        }

        $modelClass = $this->getEntityModel($entityType);
        $entity = $modelClass::findOrFail($this->pendingEntityId);

        // Update entity record
        $entity->update([
            'name' => $this->editingValue,
        ]);

        // Display success toast message
        $entityLabel = str_replace('_', ' ', $entityType);
        // Remove trailing 's' or 'ies' and make singular
        if (str_ends_with($entityLabel, 'ies')) {
            $entityLabel = substr($entityLabel, 0, -3).'y';
        } else {
            $entityLabel = rtrim($entityLabel, 's');
        }
        $entityLabel = ucfirst($entityLabel); // Capitalize
        $this->dispatch('toast', message: __($entityLabel.' updated successfully.'), type: 'success');

        // Reset editing state
        $this->cancelEditing();

        // Clean up session
        session()->forget('pending_entity_type');
    }

    private function deleteEntity(): void
    {
        $entityType = session()->get('pending_entity_type');

        if (! $entityType) {
            $this->dispatch('toast', message: __('Invalid entity type.'), type: 'error');

            return;
        }

        $modelClass = $this->getEntityModel($entityType);
        $entity = $modelClass::find($this->pendingEntityId);

        if (! $entity) {
            $this->dispatch('toast', message: __('Entity not found.'), type: 'error');

            return;
        }

        // Delete the entity
        $entity->delete();

        // Display success toast message
        $entityLabel = str_replace('_', ' ', $entityType);
        // Remove trailing 's' or 'ies' and make singular
        if (str_ends_with($entityLabel, 'ies')) {
            $entityLabel = substr($entityLabel, 0, -3).'y';
        } else {
            $entityLabel = rtrim($entityLabel, 's');
        }
        $entityLabel = ucfirst($entityLabel); // Capitalize
        $this->dispatch('toast', message: __($entityLabel.' deleted successfully.'), type: 'success');

        // Clean up session
        session()->forget('pending_entity_type');
    }

    // Entity helper methods
    private function getEntityQuery(string $entityType): Builder
    {
        $model = $this->getEntityModel($entityType);

        return $model::query();
    }

    private function getEntityModel(string $entityType): string
    {
        return match ($entityType) {
            'categories' => Category::class,
            'task_categories' => TaskCategory::class,
            'locations' => Location::class,
            'task_priorities' => TaskPriority::class,
            default => throw new \InvalidArgumentException("Unknown entity type: {$entityType}"),
        };
    }

    private function getRelationshipName(string $entityType): string
    {
        return match ($entityType) {
            'categories' => 'receipts',
            'task_categories' => 'tasks',
            'locations' => 'tasks',
            'task_priorities' => 'tasks',
            default => throw new \InvalidArgumentException("Unknown entity type: {$entityType}"),
        };
    }

    private function validateRelationships(string $entityType, int $id): ?string
    {
        $modelClass = $this->getEntityModel($entityType);
        $model = $modelClass::find($id);

        if (! $model) {
            return 'Entity not found';
        }

        $relationshipName = $this->getRelationshipName($entityType);
        $count = $model->$relationshipName()->count();

        if ($count > 0) {
            $entityLabel = str_replace('_', ' ', $entityType);
            $entityLabel = rtrim($entityLabel, 's'); // Remove trailing 's' for singular form

            return "Cannot delete: {$count} ".
                   str($relationshipName)->plural().
                   ' use this '.$entityLabel;
        }

        return null;
    }

    // Role color management methods
    public function startEditingColor(int $roleId, string $currentColor): void
    {
        $this->editingColorRoleId = $roleId;
        $this->editingColor = $currentColor;
    }

    public function saveColor(int $roleId): void
    {
        // Validate hex color format (#RRGGBB or #RGB)
        $this->validate([
            'editingColor' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ], [
            'editingColor.required' => 'Color is required',
            'editingColor.regex' => 'Invalid color format. Use #RRGGBB or #RGB',
        ]);

        // Show password confirmation modal before saving
        $this->pendingAction = 'save_color';
        $this->pendingEntityId = $roleId;
        $this->confirmPassword = '';
        $this->passwordError = '';
        $this->showConfirmModal = true;
    }

    private function executeSaveColor(): void
    {
        $role = Role::findOrFail($this->pendingEntityId);

        // Update Role record with new color
        $role->update([
            'color' => $this->editingColor,
        ]);

        // Display success toast message
        $this->dispatch('toast', message: __('Role color updated successfully.'), type: 'success');

        // Reset editing state
        $this->editingColorRoleId = null;
        $this->editingColor = '';
    }

    public function cancelEditingColor(): void
    {
        $this->editingColorRoleId = null;
        $this->editingColor = '';
    }

    // ─── Birthdate Management ─────────────────────────────────

    public function openBirthdateCreate(): void
    {
        $this->editingBirthdateId = null;
        $this->bdName = '';
        $this->bdDate = '';
        $this->bdUserId = null;
        $this->bdNotes = '';
        $this->showBirthdateModal = true;
    }

    public function openBirthdateEdit(int $id): void
    {
        $bd = Birthdate::findOrFail($id);
        $this->editingBirthdateId = $bd->id;
        $this->bdName = $bd->name;
        $this->bdDate = $bd->birthdate->format('Y-m-d');
        $this->bdUserId = $bd->user_id;
        $this->bdNotes = $bd->notes ?? '';
        $this->showBirthdateModal = true;
    }

    public function saveBirthdate(): void
    {
        $validated = Validator::make([
            'bdName' => $this->bdName,
            'bdDate' => $this->bdDate,
        ], [
            'bdName' => 'required|string|max:255',
            'bdDate' => 'required|date',
        ])->validate();

        $data = [
            'name' => $this->bdName,
            'birthdate' => $this->bdDate,
            'user_id' => $this->bdUserId ?: null,
            'notes' => $this->bdNotes ?: null,
        ];

        if ($this->editingBirthdateId) {
            Birthdate::findOrFail($this->editingBirthdateId)->update($data);
            $this->dispatch('toast', message: __('Birthday updated successfully.'), type: 'success');
        } else {
            Birthdate::create($data);
            $this->dispatch('toast', message: __('Birthday added successfully.'), type: 'success');
        }

        $this->showBirthdateModal = false;
        $this->editingBirthdateId = null;
    }

    public function confirmDeleteBirthdate(int $id): void
    {
        $this->confirmingDeleteBirthdateId = $id;
        $this->showDeleteBirthdateConfirm = true;
    }

    public function closeDeleteBirthdateConfirm(): void
    {
        $this->confirmingDeleteBirthdateId = null;
        $this->showDeleteBirthdateConfirm = false;
    }

    public function deleteBirthdate(): void
    {
        Birthdate::findOrFail($this->confirmingDeleteBirthdateId)->delete();
        $this->confirmingDeleteBirthdateId = null;
        $this->showDeleteBirthdateConfirm = false;
        $this->dispatch('toast', message: __('Birthday deleted successfully.'), type: 'success');
    }

    public function closeBirthdateModal(): void
    {
        $this->showBirthdateModal = false;
        $this->editingBirthdateId = null;
    }

    public function render()
    {
        $users = User::with(['role'])
            ->when($this->search['users'] ?? '', function ($query, $searchTerm) {
                $query->where('name', 'like', '%'.$searchTerm.'%')
                    ->orWhere('email', 'like', '%'.$searchTerm.'%');
            })
            ->orderBy('name')
            ->paginate(10);

        $categories = Category::query()
            ->when($this->search['categories'] ?? '', function ($query, $searchTerm) {
                $query->where('name', 'like', '%'.$searchTerm.'%');
            })
            ->orderBy('name')
            ->paginate(10);

        $taskCategories = TaskCategory::query()
            ->when($this->search['task_categories'] ?? '', function ($query, $searchTerm) {
                $query->where('name', 'like', '%'.$searchTerm.'%');
            })
            ->orderBy('name')
            ->paginate(10);

        $locations = Location::query()
            ->when($this->search['locations'] ?? '', function ($query, $searchTerm) {
                $query->where('name', 'like', '%'.$searchTerm.'%');
            })
            ->orderBy('name')
            ->paginate(10);

        $taskPriorities = TaskPriority::query()
            ->when($this->search['task_priorities'] ?? '', function ($query, $searchTerm) {
                $query->where('name', 'like', '%'.$searchTerm.'%');
            })
            ->orderBy('name')
            ->paginate(10);

        $roles = Role::query()
            ->when($this->search['role_colors'] ?? '', function ($query, $searchTerm) {
                $query->where('name', 'like', '%'.$searchTerm.'%');
            })
            ->orderBy('name')
            ->get();

        $birthdates = Birthdate::with('user')
            ->when($this->search['birthdates'] ?? '', function ($query, $searchTerm) {
                $query->where('name', 'like', '%'.$searchTerm.'%');
            })
            ->orderBy('birthdate')
            ->get();

        $allUsers = User::orderBy('name')->get();

        return view('livewire.admin.admin-panel', [
            'users' => $users,
            'categories' => $categories,
            'taskCategories' => $taskCategories,
            'locations' => $locations,
            'taskPriorities' => $taskPriorities,
            'roles' => $roles,
            'birthdates' => $birthdates,
            'allUsers' => $allUsers,
            'confirmingDeleteBirthdate' => $this->confirmingDeleteBirthdateId
                ? Birthdate::find($this->confirmingDeleteBirthdateId)
                : null,
        ]);
    }
}
