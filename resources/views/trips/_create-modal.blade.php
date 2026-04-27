<dialog id="createTripModal" class="rounded-2xl shadow-2xl w-full max-w-2xl p-0 border-0">
    <form method="POST" action="{{ route('trips.store') }}" class="bg-white rounded-2xl p-8 max-h-[90vh] overflow-y-auto">
        @csrf
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900">Plan Trip</h2>
            <button type="button" onclick="document.getElementById('createTripModal').close()" class="text-gray-400 hover:text-gray-700 text-3xl font-bold">&times;</button>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Location *</label>
            <input type="text" name="name" required placeholder="e.g., Yorkshire Moors National Park" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Description</label>
            <textarea name="description" rows="2" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Brief description of the trip"></textarea>
        </div>
        <div class="flex gap-4 mb-4">
            <div class="flex-1">
                <label class="block text-lg font-semibold mb-1">Start Date & Time *</label>
                <input type="datetime-local" name="start_date" required class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <div class="flex-1">
                <label class="block text-lg font-semibold mb-1">End Date & Time *</label>
                <input type="datetime-local" name="end_date" required class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Category *</label>
            <select name="trip_category_id" required class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Select category...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Designated Return (Buffer Alert)</label>
            <input type="datetime-local" name="buffer_alert" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-2">Participants * <span class="text-base font-normal">(select all who are going)</span></label>
            <div class="grid grid-cols-2 gap-2">
                @foreach($users as $user)
                    <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-gray-200 text-lg font-semibold cursor-pointer hover:border-blue-400">
                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="w-4 h-4">
                        <span>{{ $user->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Route Checkpoints <span class="text-base font-normal">(select planned stops)</span></label>
            <div class="space-y-2 max-h-32 overflow-y-auto">
                @foreach($checkpoints as $checkpoint)
                    <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-gray-200 cursor-pointer hover:border-purple-400">
                        <input type="checkbox" name="checkpoint_ids[]" value="{{ $checkpoint->id }}" class="w-4 h-4">
                        <span class="font-semibold">{{ $checkpoint->location }}</span>
                        @if($checkpoint->address)
                            <span class="text-sm text-gray-500">- {{ $checkpoint->address }}</span>
                        @endif
                    </label>
                @endforeach
            </div>
        </div>
        
        <!-- Temporary Checkpoints Section -->
        <div class="mb-6">
            <label class="block text-lg font-semibold mb-2">Temporary Checkpoints <span class="text-base font-normal text-gray-500">(Only for this trip)</span></label>
            <div id="tempCheckpointsContainer">
                <div class="temp-checkpoint-entry bg-gray-50 p-4 rounded-lg mb-2 border border-dashed border-gray-300">
                    <div class="flex gap-3 mb-2">
                        <input type="text" name="temp_checkpoint_names[]" placeholder="Checkpoint name (e.g., Gas Station Stop)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                        <button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-2 px-3 rounded text-sm">✕</button>
                    </div>
                    <input type="text" name="temp_checkpoint_addresses[]" placeholder="Address or coordinates (optional)" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                </div>
            </div>
            <button type="button" onclick="addTempCheckpointField()" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-bold py-2 px-4 rounded text-sm mt-2">+ Add Temporary Checkpoint</button>
        </div>

        <!-- Tip section -->
        <div class="mb-6">
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <p class="text-sm text-blue-700">
                    <strong>💡 Tip:</strong> You can upload documents and permits after creating the trip.
                </p>
            </div>
        </div>

        <!-- Submit and Cancel buttons -->
        <div class="flex justify-end gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded text-lg">Plan Trip</button>
            <button type="button" onclick="document.getElementById('createTripModal').close()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded text-lg">Cancel</button>
        </div>
    </form>
</dialog>

<script>
    function addTempCheckpointField() {
        const container = document.getElementById('tempCheckpointsContainer');
        const newGroup = document.createElement('div');
        newGroup.className = 'temp-checkpoint-entry bg-gray-50 p-4 rounded-lg mb-2 border border-dashed border-gray-300';
        newGroup.innerHTML = `
            <div class="flex gap-3 mb-2">
                <input type="text" name="temp_checkpoint_names[]" placeholder="Checkpoint name (e.g., Gas Station Stop)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-2 px-3 rounded text-sm">✕</button>
            </div>
            <input type="text" name="temp_checkpoint_addresses[]" placeholder="Address or coordinates (optional)" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400" />
        `;
        container.appendChild(newGroup);
    }
    
    // Close modal on backdrop click
    document.getElementById('createTripModal').addEventListener('click', function(event) {
        if (event.target === this) {
            this.close();
        }
    });
</script>