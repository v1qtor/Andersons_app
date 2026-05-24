@extends('layouts.app')

@section('content')
<div class="flex-1 px-8 py-8" style="background: #f8fbff;">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-extrabold text-blue-900">Saved Checkpoints</h1>
        <div class="flex gap-4">
            <a href="{{ route('trips.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded text-lg">Back to Trips</a>
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded text-lg" onclick="document.getElementById('createCheckpointModal').showModal()">+ Create Checkpoint</button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8">
        <input type="text" id="searchCheckpoints" placeholder="Search checkpoints and folders..." class="w-full px-5 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
    </div>

    @if($folders->count() > 0 || $checkpoints->count() > 0)
        @foreach($folders as $folder)
            @php
                $folderCheckpoints = $checkpoints->where('folder_id', $folder->id);
            @endphp
            @if($folderCheckpoints->count() > 0)
                <div class="bg-white rounded-xl shadow p-6 mb-6 folder-section" data-folder-section="{{ $folder->id }}" data-folder-name="{{ strtolower($folder->name) }}">
                    <div class="mb-4 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            📁 {{ $folder->name }}
                            <span class="text-base font-normal text-gray-500">({{ $folderCheckpoints->count() }} checkpoints)</span>
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 folder-content">
                        @foreach($folderCheckpoints as $checkpoint)
                            @include('checkpoints._card', ['checkpoint' => $checkpoint])
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        @php
            $unassignedCheckpoints = $checkpoints->where('folder_id', null);
        @endphp
        @if($unassignedCheckpoints->count() > 0)
            <div class="bg-white rounded-xl shadow p-6" data-folder-section="unassigned">
                <div class="mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">Unassigned Checkpoints</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($unassignedCheckpoints as $checkpoint)
                        @include('checkpoints._card', ['checkpoint' => $checkpoint])
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="bg-white rounded-xl shadow p-6 text-center text-gray-500">
            <p class="text-xl">No checkpoints created yet</p>
            <p class="mt-2">Create checkpoints here, then add them to trips!</p>
        </div>
    @endif
</div>

<!-- Create Checkpoint Modal -->
<dialog id="createCheckpointModal" class="rounded-2xl shadow-2xl w-full max-w-xl p-0 border-0">
    <form method="POST" action="{{ route('checkpoints.store') }}" class="bg-white rounded-2xl p-8">
        @csrf
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900">Create Checkpoint</h2>
            <button type="button" onclick="document.getElementById('createCheckpointModal').close()" class="text-gray-400 hover:text-gray-700 text-3xl font-bold">&times;</button>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Checkpoint Name *</label>
            <input type="text" name="location" required placeholder="e.g., Malham Cove" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Description</label>
            <input type="text" name="description" placeholder="e.g., Natural limestone formation" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Address (optional - enter to geocode)</label>
            <div class="flex gap-2">
                <input type="text" id="createCheckpointAddress" placeholder="e.g., Malham Cove, Settle BD24 9PT" class="flex-1 px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" name="address" />
                <button type="button" onclick="geocodeCheckpointField('createCheckpointAddress', 'createCheckpointLat', 'createCheckpointLng')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-4 rounded text-sm">Find</button>
            </div>
        </div>
        <div class="flex gap-4 mb-4">
            <div class="flex-1">
                <label class="block text-lg font-semibold mb-1">Latitude (auto-filled)</label>
                <input type="text" id="createCheckpointLat" name="latitude" placeholder="e.g., 54.0749" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" readonly />
            </div>
            <div class="flex-1">
                <label class="block text-lg font-semibold mb-1">Longitude (auto-filled)</label>
                <input type="text" id="createCheckpointLng" name="longitude" placeholder="e.g., -2.1628" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" readonly />
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Folder *</label>
            <select name="folder_id" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Select existing folder...</option>
                @foreach($folders as $folder)
                    <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-6">
            <label class="block text-lg font-semibold mb-1">Or Create New Folder</label>
            <input type="text" name="new_folder" placeholder="e.g., Lake District Locations" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div class="flex justify-end gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded text-lg">Save Checkpoint</button>
            <button type="button" onclick="document.getElementById('createCheckpointModal').close()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded text-lg">Cancel</button>
        </div>
    </form>
</dialog>

<!-- Edit Checkpoint Modal -->
<dialog id="editCheckpointModal" class="rounded-2xl shadow-2xl w-full max-w-xl p-0 border-0">
    <form method="POST" class="bg-white rounded-2xl p-8">
        @csrf
        @method('PUT')
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900">Edit Checkpoint</h2>
            <button type="button" onclick="document.getElementById('editCheckpointModal').close()" class="text-gray-400 hover:text-gray-700 text-3xl font-bold">&times;</button>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Checkpoint Name *</label>
            <input type="text" name="location" id="edit_location" required class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Description</label>
            <input type="text" name="description" id="edit_description" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Address</label>
            <div class="flex gap-2">
                <input type="text" name="address" id="edit_address" class="flex-1 px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
                <button type="button" onclick="geocodeCheckpointField('edit_address', 'edit_latitude', 'edit_longitude')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-4 rounded text-sm">Find</button>
            </div>
        </div>
        <div class="flex gap-4 mb-4">
            <div class="flex-1">
                <label class="block text-lg font-semibold mb-1">Latitude</label>
                <input type="text" name="latitude" id="edit_latitude" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <div class="flex-1">
                <label class="block text-lg font-semibold mb-1">Longitude</label>
                <input type="text" name="longitude" id="edit_longitude" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
        </div>
        <div class="mb-6">
            <label class="block text-lg font-semibold mb-1">Folder</label>
            <select name="folder_id" id="edit_folder_id" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">No folder</option>
                @foreach($folders as $folder)
                    <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex justify-end gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded text-lg">Update Checkpoint</button>
            <button type="button" onclick="document.getElementById('editCheckpointModal').close()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded text-lg">Cancel</button>
        </div>
    </form>
</dialog>

<script>
    function openEditCheckpoint(id, location, description, address, latitude, longitude, folderId) {
        const modal = document.getElementById('editCheckpointModal');
        const form = modal.querySelector('form');
        form.action = '/checkpoints/' + id;
        
        document.getElementById('edit_location').value = location;
        document.getElementById('edit_description').value = description || '';
        document.getElementById('edit_address').value = address || '';
        document.getElementById('edit_latitude').value = latitude || '';
        document.getElementById('edit_longitude').value = longitude || '';
        document.getElementById('edit_folder_id').value = folderId || '';
        
        modal.showModal();
    }

    document.getElementById('searchCheckpoints').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const folderSections = document.querySelectorAll('.folder-section');
        
        folderSections.forEach(folderSection => {
            const folderName = folderSection.getAttribute('data-folder-name') || '';
            const checkpointCards = folderSection.querySelectorAll('.checkpoint-card');
            let visibleCount = 0;
            
            checkpointCards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                if (searchTerm === '' || cardText.includes(searchTerm) || folderName.includes(searchTerm)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            folderSection.style.display = (searchTerm === '' || visibleCount > 0) ? '' : 'none';
        });
    });

    const modals = document.querySelectorAll('dialog');
    modals.forEach(modal => {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.close();
            }
        });
    });

    async function geocodeCheckpointField(addressFieldId, latFieldId, lngFieldId) {
        const addressInput = document.getElementById(addressFieldId);
        const latInput = document.getElementById(latFieldId);
        const lngInput = document.getElementById(lngFieldId);
        
        const address = addressInput.value.trim();
        if (!address) {
            alert('Please enter an address first');
            return;
        }

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
                alert('Location found!');
            } else {
                alert(data.message || 'Could not find address. Try a more specific location.');
            }
        } catch (error) {
            console.error('Geocoding error:', error);
            alert('Error searching for location. Please try again.');
        }
    }
</script>
@endsection