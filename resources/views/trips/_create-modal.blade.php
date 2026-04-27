<dialog id="createTripModal" class="rounded-2xl shadow-2xl w-full max-w-2xl p-0 border-0">
    <form method="POST" action="{{ route('trips.store') }}" class="bg-white rounded-2xl p-8 max-h-[90vh] overflow-y-auto">
        @csrf
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900">Plan Trip</h2>
            <button type="button" onclick="document.getElementById('createTripModal').close()" class="text-gray-400 hover:text-gray-700 text-3xl font-bold">&times;</button>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Location *</label>
            <input type="text" name="name" required placeholder="e.g., Yorkshire Moors National Park" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg" />
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Description</label>
            <textarea name="description" rows="2" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg" placeholder="Brief description"></textarea>
        </div>
        <div class="flex gap-4 mb-4">
            <div class="flex-1"><label class="block text-lg font-semibold mb-1">Start *</label><input type="datetime-local" name="start_date" required class="w-full px-4 py-3 rounded-lg border" /></div>
            <div class="flex-1"><label class="block text-lg font-semibold mb-1">End *</label><input type="datetime-local" name="end_date" required class="w-full px-4 py-3 rounded-lg border" /></div>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Category *</label>
            <select name="trip_category_id" required class="w-full px-4 py-3 rounded-lg border">
                <option value="">Select…</option>
                @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Buffer Alert</label>
            <input type="datetime-local" name="buffer_alert" class="w-full px-4 py-3 rounded-lg border" />
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-2">Participants *</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach($users as $user)
                <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-gray-200 cursor-pointer">
                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="w-4 h-4">
                    <span>{{ $user->name }}</span>
                </label>
                @endforeach
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Permanent Checkpoints</label>
            <div class="space-y-2 max-h-32 overflow-y-auto">
                @foreach($checkpoints as $cp)
                <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-gray-200 cursor-pointer">
                    <input type="checkbox" name="checkpoint_ids[]" value="{{ $cp->id }}" class="w-4 h-4">
                    <span class="font-semibold">{{ $cp->location }}</span>
                    @if($cp->address)<span class="text-sm text-gray-500">- {{ $cp->address }}</span>@endif
                </label>
                @endforeach
            </div>
        </div>

        <!-- Temporary Checkpoints -->
        <div class="mb-6">
            <label class="block text-lg font-semibold mb-2">Temporary Checkpoints <span class="text-base font-normal text-gray-500">(only this trip)</span></label>
            <div id="tempCheckpointsContainer">
                <div class="temp-checkpoint-entry bg-gray-50 p-4 rounded-lg mb-2 border border-dashed border-gray-300">
                    <div class="flex gap-3 mb-2">
                        <input type="text" name="temp_checkpoint_names[]" placeholder="Checkpoint name" class="flex-1 px-4 py-2 rounded-lg border border-gray-300" />
                        <button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-2 px-3 rounded text-sm">✕</button>
                    </div>
                    <input type="text" name="temp_checkpoint_addresses[]" placeholder="Address (optional)" class="w-full px-4 py-2 rounded-lg border border-gray-300 mb-2" />
                    <div class="flex gap-2">
                        <input type="text" name="temp_checkpoint_lat[]" placeholder="Latitude (optional)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300" />
                        <input type="text" name="temp_checkpoint_lng[]" placeholder="Longitude (optional)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300" />
                    </div>
                </div>
            </div>
            <button type="button" onclick="addTempCheckpointField()" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-bold py-2 px-4 rounded text-sm mt-2">+ Add Another</button>
        </div>

        <div class="flex justify-end gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded text-lg">Plan Trip</button>
            <button type="button" onclick="document.getElementById('createTripModal').close()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded text-lg">Cancel</button>
        </div>
    </form>
</dialog>

<script>
function addTempCheckpointField() {
    const container = document.getElementById('tempCheckpointsContainer');
    const div = document.createElement('div');
    div.className = 'temp-checkpoint-entry bg-gray-50 p-4 rounded-lg mb-2 border border-dashed border-gray-300';
    div.innerHTML = `
        <div class="flex gap-3 mb-2">
            <input type="text" name="temp_checkpoint_names[]" placeholder="Checkpoint name" class="flex-1 px-4 py-2 rounded-lg border border-gray-300" />
            <button type="button" onclick="this.parentElement.parentElement.remove()" class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-2 px-3 rounded text-sm">✕</button>
        </div>
        <input type="text" name="temp_checkpoint_addresses[]" placeholder="Address (optional)" class="w-full px-4 py-2 rounded-lg border border-gray-300 mb-2" />
        <div class="flex gap-2">
            <input type="text" name="temp_checkpoint_lat[]" placeholder="Latitude (optional)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300" />
            <input type="text" name="temp_checkpoint_lng[]" placeholder="Longitude (optional)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300" />
        </div>
    `;
    container.appendChild(div);
}
</script>