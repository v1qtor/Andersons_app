@extends('layouts.app')

@section('content')
<div class="py-4 px-2" style="background: #f8fbff; font-size: 0.95rem;">
    <h1 class="text-3xl font-extrabold mb-4 text-blue-900" style="font-size: 2rem;">Trip Management</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="mb-6 flex items-center gap-3">
        <label for="trip-filter" class="text-base font-semibold text-gray-800">Show:</label>
        <select id="trip-filter" class="text-base px-3 py-1.5 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="all">All Trips</option>
            <option value="upcoming">Upcoming</option>
            <option value="active">Currently in Action</option>
            <option value="completed">Past Trips</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div class="mb-4">
        <details class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-900 p-3 rounded" open>
            <summary class="cursor-pointer text-lg font-bold select-none" style="font-size: 1.1rem; color: #b45309;">Important Trip Information</summary>
            <ul class="list-disc ml-5 mt-3 text-base" style="font-size: 1.05rem; color: #78350f;">
                <li>Set a <strong>Designated Return Time</strong> – you'll receive alerts if travelers haven't checked in by then</li>
                <li><span class="text-red-700 font-bold">RED color is ONLY used for overdue return time warnings</span></li>
                <li>Add checkpoints to track the route and key locations</li>
                <li>Always upload necessary permits and emergency contact information</li>
            </ul>
        </details>
    </div>

    <div class="flex justify-between items-center mb-3">
        <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2 text-base" style="font-size: 1rem;" onclick="window.location.href='{{ route('checkpoints.index') }}'">Checkpoints</button>
        <button class="bg-blue-700 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded text-base border border-blue-900 shadow transition duration-150" style="font-size: 1rem; background-color: #1d4ed8 !important;" onclick="document.getElementById('createTripModal').showModal()">+ Plan Trip</button>
    </div>
    
    @include('trips._create-modal')

    @forelse($trips as $trip)
        @php
            $isOverdue = $trip->buffer_alert && now() > $trip->buffer_alert;
            $statusName = $trip->status->name ?? 'upcoming';
            $statusClass = match($statusName) {
                'active' => 'bg-green-600 text-white',
                'upcoming' => 'bg-blue-600 text-white',
                'completed' => 'bg-gray-600 text-white',
                'cancelled' => 'bg-red-600 text-white',
                default => 'bg-gray-600 text-white'
            };
        @endphp
        <div class="trip-card bg-white rounded-xl shadow p-6 mb-6 border-2 {{ $isOverdue ? 'border-red-200' : 'border-gray-200' }}" data-status="{{ $statusName }}" data-trip-id="{{ $trip->id }}">
            <div class="flex justify-between items-center mb-3">
                <div>
                    <span class="text-2xl font-extrabold text-blue-900" style="font-size: 1.5rem;">{{ $trip->name }}</span>
                    <span class="ml-3 px-3 py-1.5 rounded text-lg font-extrabold align-middle shadow-lg" style="font-size: 1.1rem; letter-spacing: 1px; {{ $isOverdue ? 'background-color: #dc2626; color: white;' : 'background-color: ' . match($statusName) { 'active' => '#16a34a', 'upcoming' => '#2563eb', 'completed' => '#4b5563', 'cancelled' => '#dc2626', default => '#4b5563' } . '; color: white;' }}">
                        {{ $isOverdue ? 'OVERDUE RETURN!' : strtoupper($statusName) }}
                    </span>
                </div>
                <div>
                    <button onclick="openEditModal({{ $trip->id }}, '{{ addslashes($trip->name) }}', '{{ addslashes($trip->description ?? '') }}', '{{ $trip->start_date->format('Y-m-d\TH:i') }}', '{{ $trip->end_date->format('Y-m-d\TH:i') }}', '{{ $trip->trip_category_id }}', '{{ $trip->buffer_alert ? $trip->buffer_alert->format('Y-m-d\TH:i') : '' }}')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1.5 px-4 rounded mr-2 text-base">Update Trip</button>
                    @if($statusName !== 'cancelled')
                        <form action="{{ url('/trips/'.$trip->id.'/cancel') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel this trip?')">
                            @csrf
                            <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-1.5 px-4 rounded mr-2 text-base">Cancel Trip</button>
                        </form>
                    @endif
                    <form action="{{ url('/trips/'.$trip->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this trip?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 text-xl font-bold">&times;</button>
                    </form>
                </div>
            </div>
            <div class="text-gray-700 mb-3 text-base" style="font-size: 1rem;">
                {{ $trip->start_date->format('d F Y') }} &rarr; {{ $trip->end_date->format('d F Y') }} <br>
                <span class="text-sm">Duration: {{ $trip->start_date->diffInDays($trip->end_date) }} days</span>
                @if($trip->buffer_alert)
                    <br><span class="text-sm font-semibold">Designated Return: {{ $trip->buffer_alert->format('d F Y H:i') }}</span>
                @endif
            </div>
            <div class="mb-3">
                @php
                    $totalCheckpoints = $trip->checkpoints->count();
                    $confirmedCheckpoints = $trip->checkpoints->where('pivot.is_confirmed', true)->count();
                    $progressPercent = $totalCheckpoints > 0 ? ($confirmedCheckpoints / $totalCheckpoints) * 100 : 0;
                @endphp
                <div class="text-sm text-gray-600">Route Progress: <span class="font-bold progress-text">{{ $confirmedCheckpoints }} of {{ $totalCheckpoints }} checkpoints reached</span></div>
                <div class="w-full bg-gray-200 rounded h-2 mt-1">
                    <div class="bg-blue-400 h-2 rounded progress-bar" style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>
            <div class="mb-3">
                <span class="font-bold text-base">Participants:</span>
                <span class="ml-2 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">{{ $trip->users->count() }} people</span>
                <div class="mt-2 flex gap-2 flex-wrap">
                    @foreach($trip->users as $user)
                        <span class="bg-purple-200 text-purple-800 px-3 py-1 rounded-full text-sm">{{ $user->name }}</span>
                    @endforeach
                </div>
            </div>
            @if($isOverdue)
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4" style="font-size: 1rem;">
                    <strong>OVERDUE - Designated Return Time:</strong><br>
                    {{ $trip->buffer_alert->format('l d F \a\t H:i') }}<br>
                    <span class="font-semibold">Travelers have not checked in. Please contact them immediately!</span>
                </div>
            @endif
            <div class="mb-4">
                <span class="font-bold text-base">Route Checkpoints:</span>
                <div class="mt-2 space-y-2" data-trip-checkpoints="{{ $trip->id }}">
                    @foreach($trip->checkpoints as $index => $checkpoint)
                        <div class="checkpoint-item {{ $checkpoint->pivot->is_confirmed ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }} border rounded p-3" data-checkpoint-id="{{ $checkpoint->id }}">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1">
                                    <div class="font-bold text-base">{{ $checkpoint->location }}</div>
                                    @if($checkpoint->address)
                                        <div class="text-sm text-blue-700">{{ $checkpoint->address }}</div>
                                    @endif
                                </div>
                                <div class="flex gap-1 ml-2">
                                    @if($index > 0)
                                        <button type="button" onclick="reorderCheckpoint({{ $trip->id }}, {{ $checkpoint->id }}, 'up')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-2 rounded text-xs" title="Move up">↑</button>
                                    @endif
                                    @if($index < $trip->checkpoints->count() - 1)
                                        <button type="button" onclick="reorderCheckpoint({{ $trip->id }}, {{ $checkpoint->id }}, 'down')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-2 rounded text-xs" title="Move down">↓</button>
                                    @endif
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex gap-2">
                                    @if(!$checkpoint->pivot->is_confirmed)
                                        <button type="button" onclick="markCheckpointArrived({{ $trip->id }}, {{ $checkpoint->id }})" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1.5 px-3 rounded text-sm">Mark Arrived</button>
                                    @else
                                        <span class="text-green-600 font-bold">✓ Arrived</span>
                                    @endif
                                    <form action="{{ url('/trips/'.$trip->id.'/checkpoints/'.$checkpoint->id) }}" method="POST" onsubmit="return confirm('Remove this checkpoint?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                    </form>
                                </div>
                            </div>
                            <!-- Image Upload Section for Checkpoint -->
                            <div class="mt-2">
                                @if($checkpoint->pivot->image_path)
                                    <img src="{{ asset('storage/' . $checkpoint->pivot->image_path) }}" alt="{{ $checkpoint->location }}" class="h-32 w-full object-cover rounded mb-2" />
                                    <form action="{{ url('/trips/'.$trip->id.'/checkpoints/'.$checkpoint->id.'/remove-image') }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 text-sm font-bold hover:text-red-700">Remove Image</button>
                                    </form>
                                @else
                                    <form action="{{ url('/trips/'.$trip->id.'/checkpoints/'.$checkpoint->id.'/upload-image') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                        @csrf
                                        <div class="flex gap-2 items-center">
                                            <input type="file" name="image" accept="image/*" required class="text-sm flex-1 border border-gray-300 rounded p-1" />
                                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded text-sm">Upload</button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    <button onclick="openAddCheckpointModal({{ $trip->id }})" class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-1.5 px-3 rounded text-sm w-full text-center">+ Add Checkpoint</button>
                </div>
            </div>
            <div class="mb-4">
                <span class="font-bold text-lg">Documents & Permits:</span>
                <div class="mt-2 space-y-2">
                    @foreach($trip->attachedFiles as $file)
                        <div class="bg-gray-100 rounded p-3 text-base flex justify-between items-center">
                            <div>
                                <span>{{ $file->name }}</span>
                                <a href="{{ asset('storage/' . $file->file_path) }}" class="text-blue-600 hover:text-blue-800 ml-2 text-sm" download>Download</a>
                            </div>
                            <form action="{{ url('/trips/'.$trip->id.'/files/'.$file->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 font-bold">&times;</button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <form action="{{ url('/trips/'.$trip->id.'/files') }}" method="POST" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <div class="flex gap-2">
                        <input type="file" name="file" required class="flex-1 px-3 py-2 border border-gray-300 rounded text-base">
                        <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded text-base">Upload</button>
                    </div>
                    <div class="text-base text-gray-600 mt-2">Upload camping permits, emergency contacts, or route maps</div>
                </form>
            </div>
            <div class="mb-4">
                <span class="font-bold text-lg">Route Map:</span>
                <div id="map-{{ $trip->id }}" class="mt-2 rounded-lg border-2 border-gray-300" style="height: 350px; width: 100%; min-height: 350px;">
                    @php
                        $hasCoordinates = $trip->checkpoints->filter(function($c) {
                            return ($c->latitude && $c->longitude) || ($c->coordinates && strpos($c->coordinates, ',') !== false);
                        })->count() > 0;
                    @endphp
                    @if(!$hasCoordinates)
                        <div class="flex items-center justify-center h-full text-gray-400">
                            <p>Add coordinates to checkpoints to see the route map</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow p-6 text-center text-gray-500">
            <p class="text-xl">No trips planned yet</p>
            <p class="mt-2">Click "+ Plan Trip" to create your first trip!</p>
        </div>
    @endforelse
    
</div>

<!-- Edit Trip Modal -->
<dialog id="editTripModal" class="rounded-2xl shadow-2xl w-full max-w-2xl p-0 border-0">
    <form method="POST" class="bg-white rounded-2xl p-8 max-h-[90vh] overflow-y-auto">
        @csrf
        @method('PUT')
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900">Update Trip</h2>
            <button type="button" onclick="document.getElementById('editTripModal').close()" class="text-gray-400 hover:text-gray-700 text-3xl font-bold">&times;</button>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Location *</label>
            <input type="text" name="name" id="edit_name" required class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Description</label>
            <textarea name="description" id="edit_description" rows="2" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
        </div>
        <div class="flex gap-4 mb-4">
            <div class="flex-1">
                <label class="block text-lg font-semibold mb-1">Start Date & Time *</label>
                <input type="datetime-local" name="start_date" id="edit_start_date" required class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <div class="flex-1">
                <label class="block text-lg font-semibold mb-1">End Date & Time *</label>
                <input type="datetime-local" name="end_date" id="edit_end_date" required class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Category *</label>
            <select name="trip_category_id" id="edit_trip_category_id" required class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Designated Return (Buffer Alert)</label>
            <input type="datetime-local" name="buffer_alert" id="edit_buffer_alert" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div class="flex justify-end gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded text-lg">Update Trip</button>
            <button type="button" onclick="document.getElementById('editTripModal').close()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded text-lg">Cancel</button>
        </div>
    </form>
</dialog>

<!-- Add Checkpoint Modal -->
<dialog id="addCheckpointModal" class="rounded-2xl shadow-2xl w-full max-w-xl p-0 border-0">
    <form method="POST" action="" class="bg-white rounded-2xl p-8" id="addCheckpointForm">
        @csrf
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900">Add Checkpoint to Trip</h2>
            <button type="button" onclick="document.getElementById('addCheckpointModal').close()" class="text-gray-400 hover:text-gray-700 text-3xl font-bold">&times;</button>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-2">Permanent Checkpoint</label>
            <select name="checkpoint_id" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Choose a permanent checkpoint...</option>
                @foreach($checkpoints as $checkpoint)
                    <option value="{{ $checkpoint->id }}">{{ $checkpoint->location }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-6">
            <p class="text-center text-gray-500 font-bold mb-4">— OR —</p>
            <label class="block text-lg font-semibold mb-2">Temporary Checkpoint</label>
            <input type="text" name="temp_name" placeholder="Checkpoint name" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg mb-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            <input type="text" name="temp_address" placeholder="Address (optional)" class="w-full px-4 py-3 rounded-lg border border-gray-300 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        </div>
        <div class="flex justify-end gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded text-lg">Add Checkpoint</button>
            <button type="button" onclick="document.getElementById('addCheckpointModal').close()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded text-lg">Cancel</button>
        </div>
    </form>
</dialog>

<script>
    function openEditModal(id, name, description, startDate, endDate, categoryId, bufferAlert) {
        const modal = document.getElementById('editTripModal');
        const form = modal.querySelector('form');
        form.action = '/trips/' + id;
        
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_description').value = description || '';
        document.getElementById('edit_start_date').value = startDate;
        document.getElementById('edit_end_date').value = endDate;
        document.getElementById('edit_trip_category_id').value = categoryId;
        document.getElementById('edit_buffer_alert').value = bufferAlert || '';
        
        modal.showModal();
        document.body.style.overflow = 'hidden';
    }

    function openAddCheckpointModal(tripId) {
        const modal = document.getElementById('addCheckpointModal');
        const form = modal.querySelector('form');
        form.action = '/trips/' + tripId + '/checkpoints';
        modal.showModal();
        document.body.style.overflow = 'hidden';
    }

    function markCheckpointArrived(tripId, checkpointId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        fetch('/trips/' + tripId + '/checkpoints/' + checkpointId + '/arrive', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            location.reload();
        });
    }

    function reorderCheckpoint(tripId, checkpointId, direction) {
        const container = document.querySelector('[data-trip-checkpoints="' + tripId + '"]');
        const items = Array.from(container.querySelectorAll('.checkpoint-item'));
        const currentIndex = items.findIndex(el => el.dataset.checkpointId == checkpointId);
        
        if (direction === 'up' && currentIndex > 0) {
            const item = items.splice(currentIndex, 1)[0];
            items.splice(currentIndex - 1, 0, item);
        } else if (direction === 'down' && currentIndex < items.length - 1) {
            const item = items.splice(currentIndex, 1)[0];
            items.splice(currentIndex + 1, 0, item);
        } else {
            return;
        }
        
        container.innerHTML = '';
        items.forEach(item => container.appendChild(item));
        
        const order = items.map(el => el.dataset.checkpointId);
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        fetch('/trips/' + tripId + '/checkpoints/reorder', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order: order })
        });
    }

    // Trip filter functionality
    document.getElementById('trip-filter').addEventListener('change', function() {
        const filter = this.value;
        const cards = document.querySelectorAll('.trip-card');
        
        cards.forEach(card => {
            if (filter === 'all') {
                card.style.display = '';
            } else {
                card.style.display = card.dataset.status === filter ? '' : 'none';
            }
        });
    });

    // Initialize maps
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($trips as $trip)
            @php
                $tripCheckpoints = $trip->checkpoints->filter(function($c) {
                    return ($c->latitude && $c->longitude) || ($c->coordinates && strpos($c->coordinates, ',') !== false);
                });
            @endphp
            @if($tripCheckpoints->count() > 0)
                initMap({{ $trip->id }}, {!! json_encode($tripCheckpoints->map(function($c) {
                    $coords = $c->coordinates ? explode(',', $c->coordinates) : [$c->latitude, $c->longitude];
                    return [
                        'name' => $c->location,
                        'lat' => floatval($coords[0]),
                        'lng' => floatval($coords[1]),
                        'is_confirmed' => $c->pivot->is_confirmed
                    ];
                })->values()->toArray()) !!});
            @endif
        @endforeach
    });

    function initMap(tripId, checkpoints) {
        if (!checkpoints || checkpoints.length === 0) return;
        
        const mapContainer = document.getElementById('map-' + tripId);
        if (!mapContainer) return;
        
        const lats = checkpoints.map(c => c.lat);
        const lngs = checkpoints.map(c => c.lng);
        
        const map = L.map('map-' + tripId);
        map.fitBounds([
            [Math.min(...lats), Math.min(...lngs)],
            [Math.max(...lats), Math.max(...lngs)]
        ]);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        const coordinates = checkpoints.map(c => [c.lat, c.lng]);
        L.polyline(coordinates, { color: 'blue', weight: 3, opacity: 0.7 }).addTo(map);
        
        checkpoints.forEach((c, i) => {
            L.marker([c.lat, c.lng])
                .bindPopup('<strong>' + (i + 1) + '. ' + c.name + '</strong>')
                .addTo(map);
        });
    }

    // Modal setup
    const modals = document.querySelectorAll('dialog');
    modals.forEach(modal => {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.close();
            }
        });
    });
</script>
@endsection