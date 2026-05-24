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
                        <input type="text" name="temp_checkpoint_names[]" placeholder="Checkpoint name" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 font-semibold" />
                        <button type="button" onclick="this.closest('.temp-checkpoint-entry').remove()" class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-2 px-3 rounded text-sm">Remove</button>
                    </div>
                    <div class="flex gap-2 mb-2">
                        <input type="text" name="temp_checkpoint_addresses[]" placeholder="Address (optional - enter to geocode)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 address-field" />
                        <button type="button" onclick="geocodeCheckpoint(this)" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-3 rounded text-sm">Find Location</button>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" name="temp_checkpoint_lat[]" placeholder="Latitude (auto-filled)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 latitude-field" readonly />
                        <input type="text" name="temp_checkpoint_lng[]" placeholder="Longitude (auto-filled)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 longitude-field" readonly />
                    </div>
                </div>
            </div>
            <button type="button" onclick="addTempCheckpointField()" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-bold py-2 px-4 rounded text-sm mt-2">+ Add Another</button>
        </div>

        <!-- Plus-Ones -->
        <div class="mb-6">
            <label class="block text-lg font-semibold mb-2">Plus-Ones (optional)</label>
            <div id="plusOnesContainer"></div>
            <button type="button" onclick="addPlusOneField()" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-bold py-2 px-4 rounded text-sm mt-2">+ Add Guest</button>
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
        <div class="flex gap-2 mb-2">
            <input type="text" name="temp_checkpoint_addresses[]" placeholder="Address (optional - enter to geocode)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 address-field" />
            <button type="button" onclick="geocodeCheckpoint(this)" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-3 rounded text-sm">Find Location</button>
        </div>
        <div class="flex gap-2">
            <input type="text" name="temp_checkpoint_lat[]" placeholder="Latitude (auto-filled)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 latitude-field" readonly />
            <input type="text" name="temp_checkpoint_lng[]" placeholder="Longitude (auto-filled)" class="flex-1 px-4 py-2 rounded-lg border border-gray-300 longitude-field" readonly />
        </div>
    `;
    container.appendChild(div);
}

async function geocodeCheckpoint(button) {
    const entry = button.closest('.temp-checkpoint-entry');
    const addressInput = entry.querySelector('.address-field');
    const latInput = entry.querySelector('.latitude-field');
    const lngInput = entry.querySelector('.longitude-field');
    
    const address = addressInput.value.trim();
    if (!address) {
        alert('Please enter an address first');
        return;
    }

    button.disabled = true;
    button.textContent = 'Searching...';

    try {
        const response = await fetch('/api/geocode-address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            body: JSON.stringify({ address })
        });

        const data = await response.json();
        if (data.success) {
            latInput.value = data.data.latitude;
            lngInput.value = data.data.longitude;
            button.textContent = '✓ Found';
            button.classList.add('bg-green-100', 'text-green-700', 'hover:bg-green-200');
            button.classList.remove('bg-blue-100', 'text-blue-700', 'hover:bg-blue-200');
            setTimeout(() => {
                button.textContent = 'Find Location';
                button.classList.remove('bg-green-100', 'text-green-700', 'hover:bg-green-200');
                button.classList.add('bg-blue-100', 'text-blue-700', 'hover:bg-blue-200');
            }, 2000);
        } else {
            alert(data.message || 'Could not find address. Try a more specific location.');
            button.textContent = 'Find Location';
        }
    } catch (error) {
        console.error('Geocoding error:', error);
        alert('Error searching for location. Please try again.');
        button.textContent = 'Find Location';
    } finally {
        button.disabled = false;
    }
}

async function geocodeAddCheckpoint() {
    const address = document.getElementById('addCheckpointAddress').value.trim();
    if (!address) {
        alert('Please enter an address first');
        return;
    }

    const button = event.target;
    button.disabled = true;
    button.textContent = 'Searching...';

    try {
        const response = await fetch('/api/geocode-address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            body: JSON.stringify({ address })
        });

        const data = await response.json();
        if (data.success) {
            document.getElementById('addCheckpointLat').value = data.data.latitude;
            document.getElementById('addCheckpointLng').value = data.data.longitude;
            button.textContent = '✓ Found';
            button.classList.add('bg-green-100', 'text-green-700', 'hover:bg-green-200');
            button.classList.remove('bg-blue-100', 'text-blue-700', 'hover:bg-blue-200');
            setTimeout(() => {
                button.textContent = 'Find';
                button.classList.remove('bg-green-100', 'text-green-700', 'hover:bg-green-200');
                button.classList.add('bg-blue-100', 'text-blue-700', 'hover:bg-blue-200');
            }, 2000);
        } else {
            alert(data.message || 'Could not find address. Try a more specific location.');
            button.textContent = 'Find';
        }
    } catch (error) {
        console.error('Geocoding error:', error);
        alert('Error searching for location. Please try again.');
        button.textContent = 'Find';
    } finally {
        button.disabled = false;
    }
}

function addPlusOneField() {
    const container = document.getElementById('plusOnesContainer');
    const index = container.querySelectorAll('.plus-one-entry').length;
    const div = document.createElement('div');
    div.className = 'plus-one-entry bg-gray-50 p-4 rounded-lg mb-2 border border-dashed border-gray-300';
    div.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <span class="font-semibold">Guest ${index + 1}</span>
            <button type="button" onclick="this.closest('.plus-one-entry').remove()" class="text-red-600 hover:text-red-800 font-bold">Remove</button>
        </div>
        <input type="text" name="plus_one_names[]" placeholder="Name (required)" class="w-full px-4 py-2 rounded-lg border border-gray-300 mb-2" required />
        <input type="email" name="plus_one_emails[]" placeholder="Email" class="w-full px-4 py-2 rounded-lg border border-gray-300 mb-2" />
        <input type="tel" name="plus_one_phones[]" placeholder="Phone" class="w-full px-4 py-2 rounded-lg border border-gray-300" />
    `;
    container.appendChild(div);
}
</script>