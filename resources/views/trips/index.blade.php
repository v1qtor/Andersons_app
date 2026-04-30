@extends('layouts.app')

@section('content')
<div class="py-4 px-2" style="background: #f8fbff; font-size: 0.95rem;">
    <h1 class="text-3xl font-extrabold mb-4 text-blue-900">Trip Management</h1>

    @if(session('success'))<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif

    <div class="mb-6 flex items-center gap-3">
        <label class="text-base font-semibold text-gray-800">Show:</label>
        <select id="trip-filter" class="text-base px-3 py-1.5 rounded border">
            <option value="all">All</option>
            <option value="upcoming">Upcoming</option>
            <option value="active">Active</option>
            <option value="completed">Past</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div class="flex justify-between items-center mb-3">
        <a href="{{ route('checkpoints.index') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Checkpoints</a>
        <button onclick="document.getElementById('createTripModal').showModal()" class="bg-blue-700 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded">+ Plan Trip</button>
    </div>

    @include('trips._create-modal')

    @forelse($trips as $trip)
        @php
            $isOverdue = $trip->buffer_alert && now() > $trip->buffer_alert;
            $statusName = $trip->status->name ?? 'upcoming';
            $userIds = $trip->users->pluck('id')->toArray();
            $duration = (int) $trip->start_date->diffInDays($trip->end_date);
        @endphp
        <div class="trip-card bg-white rounded-xl shadow p-6 mb-6 border-2 {{ $isOverdue ? 'border-red-200' : 'border-gray-200' }}" data-status="{{ $statusName }}" data-trip-id="{{ $trip->id }}">
            <div class="flex justify-between items-center mb-3">
                <div>
                    <span class="text-2xl font-extrabold text-blue-900">{{ $trip->name }}</span>
                    <span class="ml-3 px-3 py-1.5 rounded text-lg font-extrabold shadow" style="{{ $isOverdue ? 'background:#dc2626;color:white;' : 'background:'.(match($statusName){'active'=>'#16a34a','upcoming'=>'#2563eb','completed'=>'#4b5563','cancelled'=>'#dc2626',default=>'#4b5563'}).';color:white;' }}">{{ $isOverdue ? 'OVERDUE!' : strtoupper($statusName) }}</span>
                </div>
                <div>
                    <button onclick="openEditModal({{ $trip->id }}, '{{ addslashes($trip->name) }}', '{{ addslashes($trip->description ?? '') }}', '{{ $trip->start_date->format('Y-m-d\TH:i') }}', '{{ $trip->end_date->format('Y-m-d\TH:i') }}', '{{ $trip->trip_category_id }}', '{{ $trip->buffer_alert ? $trip->buffer_alert->format('Y-m-d\TH:i') : '' }}', {{ json_encode($userIds) }})" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1.5 px-4 rounded mr-2">Update</button>
                    @if($statusName !== 'cancelled')
                    <form action="{{ url('/trips/'.$trip->id.'/cancel') }}" method="POST" class="inline" onsubmit="return confirm('Cancel trip?')">
                        @csrf <button class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-1.5 px-4 rounded mr-2">Cancel Trip</button>
                    </form>
                    @endif
                    <form action="{{ url('/trips/'.$trip->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE') <button class="text-red-500 text-xl font-bold">&times;</button>
                    </form>
                </div>
            </div>
            <div class="text-gray-700 mb-3">
                {{ $trip->start_date->format('d F Y') }} &rarr; {{ $trip->end_date->format('d F Y') }}<br>
                <span class="text-sm">Duration: {{ $duration }} {{ Str::plural('day', $duration) }}</span>
                @if($trip->buffer_alert)<br><span class="text-sm font-semibold">Return: {{ $trip->buffer_alert->format('d F Y H:i') }}</span>@endif
            </div>
            <div class="mb-3">
                @php $total = $trip->checkpoints->count(); $confirmed = $trip->checkpoints->where('pivot.is_confirmed', true)->count(); $pct = $total>0 ? ($confirmed/$total)*100 : 0; @endphp
                <div class="text-sm text-gray-600">Route Progress: <span class="font-bold progress-text">{{ $confirmed }} of {{ $total }} reached</span></div>
                <div class="w-full bg-gray-200 rounded h-2 mt-1"><div class="bg-blue-400 h-2 rounded progress-bar" style="width:{{ $pct }}%"></div></div>
            </div>
            <div class="mb-3"><span class="font-bold">Participants:</span> <span class="ml-2 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">{{ $trip->users->count() }} people</span>
                <div class="mt-2 flex gap-2 flex-wrap">@foreach($trip->users as $u)<span class="bg-purple-200 text-purple-800 px-3 py-1 rounded-full text-sm">{{ $u->name }}</span>@endforeach</div>
            </div>

            <!-- OVERDUE BANNER (restored) -->
            @if($isOverdue)
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4">
                <strong>⚠️ OVERDUE - Designated Return Time:</strong><br>
                {{ $trip->buffer_alert->format('l d F \a\t H:i') }}<br>
                <span class="font-semibold">Travelers have not checked in. Please contact them immediately!</span>
            </div>
            @endif

            <div class="mb-4"><span class="font-bold text-base">Route Checkpoints:</span>
                <div class="mt-2 space-y-2" data-trip-checkpoints="{{ $trip->id }}">
                    @foreach($trip->checkpoints as $idx => $cp)
                    <div class="checkpoint-item {{ $cp->pivot->is_confirmed ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }} border rounded p-3" data-checkpoint-id="{{ $cp->id }}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1"><div class="font-bold text-base">
                                @if($cp->pivot->is_temporary)
                                <button onclick="openEditTempCheckpoint({{ $cp->id }}, '{{ addslashes($cp->location) }}', '{{ addslashes($cp->address ?? '') }}', '{{ $cp->latitude ?? '' }}', '{{ $cp->longitude ?? '' }}')" 
                                        class="text-xs text-indigo-600 hover:text-indigo-800 underline ml-2">Edit</button>
                                @endif
                                {{ $cp->location }}
                            </div>@if($cp->address)<div class="text-sm text-blue-700">{{ $cp->address }}</div>@endif</div>
                            <div class="flex gap-1 ml-2">
                                <button onclick="reorderCheckpoint({{ $trip->id }},{{ $cp->id }},'up')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-2 rounded text-xs">↑</button>
                                <button onclick="reorderCheckpoint({{ $trip->id }},{{ $cp->id }},'down')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-1 px-2 rounded text-xs">↓</button>
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <div class="flex gap-2 items-center">
                                @if(!$cp->pivot->is_confirmed)
                                <button onclick="markCheckpointArrived({{ $trip->id }},{{ $cp->id }})" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1.5 px-3 rounded text-sm">Mark Arrived</button>
                                @else
                                <span class="text-green-600 font-bold">✓ Arrived</span>
                                <button onclick="unmarkCheckpoint({{ $trip->id }},{{ $cp->id }})" class="text-xs text-blue-600 hover:text-blue-800 underline ml-2">Undo</button>
                                @endif
                                <form action="{{ url('/trips/'.$trip->id.'/checkpoints/'.$cp->id) }}" method="POST" onsubmit="return confirm('Remove this checkpoint?')" class="inline ml-3">
                                    @csrf @method('DELETE') <button class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-sm">Remove</button>
                                </form>
                            </div>
                        </div>
                        @if($cp->pivot->image_path)
                        <div class="mt-2">
                            <img src="{{ Storage::url($cp->pivot->image_path) }}" class="h-32 w-full object-cover rounded mb-2"/>
                            <div class="flex gap-2">
                                <a href="{{ Storage::url($cp->pivot->image_path) }}" target="_blank" class="text-blue-500 text-sm underline">View full image</a>
                                <form action="{{ url('/trips/'.$trip->id.'/checkpoints/'.$cp->id.'/remove-image') }}" method="POST">@csrf @method('DELETE')<button class="text-red-500 text-sm">Remove Image</button></form>
                            </div>
                        </div>
                        @else
                        <form action="{{ url('/trips/'.$trip->id.'/checkpoints/'.$cp->id.'/upload-image') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                            @csrf
                            <div class="flex gap-2 items-center"><input type="file" name="image" accept="image/*" required class="text-sm flex-1 border rounded p-1"/><button class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded text-sm">Upload</button></div>
                        </form>
                        @endif
                    </div>
                @endforeach
                <button onclick="openAddCheckpointModal({{ $trip->id }})" class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-1.5 px-3 rounded text-sm w-full">+ Add Checkpoint</button>
            </div>
        </div>

        <div class="mb-4"><span class="font-bold text-lg">Documents & Permits:</span>
            <div class="mt-2 space-y-2">
                @foreach($trip->attachedFiles as $file)
                <div class="bg-gray-100 rounded p-3 flex justify-between items-center">
                    <div><span>{{ $file->name }}</span> <a href="{{ Storage::url($file->file_path) }}" download class="text-blue-600 ml-2 text-sm">Download</a></div>
                    <form action="{{ url('/trips/'.$trip->id.'/files/'.$file->id) }}" method="POST" class="inline">@csrf @method('DELETE')<button class="text-red-500 font-bold">&times;</button></form>
                </div>
                @endforeach
            </div>
            <form action="{{ url('/trips/'.$trip->id.'/files') }}" method="POST" enctype="multipart/form-data" class="mt-3">@csrf
                <div class="flex gap-2"><input type="file" name="file" required class="flex-1 px-3 py-2 border rounded"><button class="bg-blue-700 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded">Upload</button></div>
            </form>
        </div>

        <div class="mb-4"><span class="font-bold text-lg">Route Map:</span>
            <div id="map-{{ $trip->id }}" class="mt-2 rounded-lg border-2 border-gray-300" style="height:350px; width:100%; min-height:350px;">
                @if($trip->checkpoints->where(fn($c)=>$c->latitude && $c->longitude)->count()==0)
                <div class="flex items-center justify-center h-full text-gray-400"><p>Add coordinates to see map</p></div>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="bg-white rounded-xl shadow p-6 text-center text-gray-500"><p>No trips yet</p></div>
@endforelse
</div>

<!-- Edit Trip Modal -->
<dialog id="editTripModal" class="rounded-2xl shadow-2xl w-full max-w-2xl p-0 border-0">
    <form method="POST" class="bg-white rounded-2xl p-8 max-h-[90vh] overflow-y-auto" onsubmit="setTimeout(()=>this.closest('dialog').close(),100)">
        @csrf @method('PUT')
        <div class="flex justify-between items-center mb-6"><h2 class="text-3xl font-extrabold">Update Trip</h2><button type="button" onclick="this.closest('dialog').close()" class="text-gray-400 hover:text-gray-700 text-3xl">&times;</button></div>
        <div class="mb-4"><label class="block text-lg font-semibold mb-1">Name *</label><input type="text" name="name" id="edit_name" required class="w-full px-4 py-3 rounded-lg border" /></div>
        <div class="mb-4"><label class="block text-lg font-semibold mb-1">Description</label><textarea name="description" id="edit_description" rows="2" class="w-full px-4 py-3 rounded-lg border"></textarea></div>
        <div class="flex gap-4 mb-4">
            <div class="flex-1"><label class="block text-lg font-semibold mb-1">Start *</label><input type="datetime-local" name="start_date" id="edit_start_date" required class="w-full px-4 py-3 rounded-lg border" /></div>
            <div class="flex-1"><label class="block text-lg font-semibold mb-1">End *</label><input type="datetime-local" name="end_date" id="edit_end_date" required class="w-full px-4 py-3 rounded-lg border" /></div>
        </div>
        <div class="mb-4"><label class="block text-lg font-semibold mb-1">Category *</label><select name="trip_category_id" id="edit_trip_category_id" required class="w-full px-4 py-3 rounded-lg border">@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></div>
        <div class="mb-4"><label class="block text-lg font-semibold mb-1">Buffer Alert</label><input type="datetime-local" name="buffer_alert" id="edit_buffer_alert" class="w-full px-4 py-3 rounded-lg border" /></div>
        <div class="mb-4"><label class="block text-lg font-semibold mb-2">Participants</label><div class="grid grid-cols-2 gap-2" id="edit-participants-container"></div></div>
        <div class="flex justify-end gap-4"><button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded">Update Trip</button><button type="button" onclick="this.closest('dialog').close()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded">Cancel</button></div>
    </form>
</dialog>

<!-- Add Checkpoint Modal -->
<dialog id="addCheckpointModal" class="rounded-2xl shadow-2xl w-full max-w-xl p-0 border-0">
    <form method="POST" action="" id="addCheckpointForm" class="bg-white rounded-2xl p-8">
        @csrf
        <div class="flex justify-between items-center mb-6"><h2 class="text-3xl font-extrabold">Add Checkpoint</h2><button type="button" onclick="this.closest('dialog').close()" class="text-gray-400 hover:text-gray-700 text-3xl">&times;</button></div>
        <div class="mb-4"><label class="block text-lg font-semibold mb-2">Permanent</label><select name="checkpoint_id" class="w-full px-4 py-3 rounded-lg border"><option value="">Choose…</option>@foreach($checkpoints as $cp)<option value="{{ $cp->id }}">{{ $cp->location }}</option>@endforeach</select></div>
        <div class="mb-6"><p class="text-center font-bold mb-4">— OR —</p><label class="block text-lg font-semibold mb-2">Temporary</label><input type="text" name="temp_name" placeholder="Name" class="w-full px-4 py-3 rounded-lg border mb-2"/><input type="text" name="temp_address" placeholder="Address" class="w-full px-4 py-3 rounded-lg border mb-2"/><div class="flex gap-2"><input type="text" name="temp_lat" placeholder="Latitude" class="flex-1 px-4 py-3 rounded-lg border"/><input type="text" name="temp_lng" placeholder="Longitude" class="flex-1 px-4 py-3 rounded-lg border"/></div></div>
        <div class="flex justify-end gap-4"><button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded">Add</button><button type="button" onclick="this.closest('dialog').close()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded">Cancel</button></div>
    </form>
</dialog>

<!-- Edit Temporary Checkpoint Modal -->
<dialog id="editTempCheckpointModal" class="rounded-2xl shadow-2xl w-full max-w-xl p-0 border-0">
    <form method="POST" action="" id="editTempCheckpointForm" class="bg-white rounded-2xl p-8">
        @csrf
        @method('PUT')
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-extrabold">Edit Temporary Checkpoint</h2>
            <button type="button" onclick="document.getElementById('editTempCheckpointModal').close()" class="text-gray-400 hover:text-gray-700 text-3xl font-bold">&times;</button>
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Name</label>
            <input type="text" name="location" id="edit-temp-location" required class="w-full px-4 py-3 rounded-lg border" />
        </div>
        <div class="mb-4">
            <label class="block text-lg font-semibold mb-1">Address</label>
            <input type="text" name="address" id="edit-temp-address" class="w-full px-4 py-3 rounded-lg border" />
        </div>
        <div class="flex gap-4 mb-4">
            <div class="flex-1">
                <label class="block text-lg font-semibold mb-1">Latitude</label>
                <input type="text" name="latitude" id="edit-temp-lat" class="w-full px-4 py-3 rounded-lg border" />
            </div>
            <div class="flex-1">
                <label class="block text-lg font-semibold mb-1">Longitude</label>
                <input type="text" name="longitude" id="edit-temp-lng" class="w-full px-4 py-3 rounded-lg border" />
            </div>
        </div>
        <div class="flex justify-end gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded text-lg">Update</button>
            <button type="button" onclick="document.getElementById('editTempCheckpointModal').close()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded text-lg">Cancel</button>
        </div>
    </form>
</dialog>

<script>
let allUsers = @json($users);

function openEditModal(id, name, desc, start, end, cat, buffer, userIds) {
    const modal = document.getElementById('editTripModal');
    modal.querySelector('form').action = '/trips/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = desc;
    document.getElementById('edit_start_date').value = start;
    document.getElementById('edit_end_date').value = end;
    document.getElementById('edit_trip_category_id').value = cat;
    document.getElementById('edit_buffer_alert').value = buffer;
    let container = document.getElementById('edit-participants-container');
    container.innerHTML = '';
    allUsers.forEach(u => {
        let checked = userIds.includes(u.id) ? 'checked' : '';
        container.innerHTML += `<label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-gray-200 cursor-pointer"><input type="checkbox" name="user_ids[]" value="${u.id}" ${checked} class="w-4 h-4"><span>${u.name}</span></label>`;
    });
    modal.showModal();
    document.body.style.overflow = 'hidden';
}

function openAddCheckpointModal(tripId) {
    const modal = document.getElementById('addCheckpointModal');
    modal.querySelector('form').action = '/trips/' + tripId + '/checkpoints';
    modal.showModal();
    document.body.style.overflow = 'hidden';
}

function markCheckpointArrived(tripId, cpId) {
    fetch(`/trips/${tripId}/checkpoints/${cpId}/arrive`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json'}
    }).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}

function unmarkCheckpoint(tripId, cpId) {
    fetch(`/trips/${tripId}/checkpoints/${cpId}/unarrive`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json'}
    }).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}

function reorderCheckpoint(tripId, cpId, dir) {
    let container = document.querySelector(`[data-trip-checkpoints="${tripId}"]`);
    let items = Array.from(container.querySelectorAll('.checkpoint-item'));
    let idx = items.findIndex(el=>el.dataset.checkpointId == cpId);
    if(dir==='up' && idx>0) { let [item] = items.splice(idx,1); items.splice(idx-1,0,item); }
    else if(dir==='down' && idx<items.length-1) { let [item] = items.splice(idx,1); items.splice(idx+1,0,item); }
    else return;
    container.innerHTML = ''; items.forEach(i=>container.appendChild(i));
    let order = items.map(i=>i.dataset.checkpointId);
    fetch(`/trips/${tripId}/checkpoints/reorder`, {
        method:'POST',
        headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json'},
        body:JSON.stringify({order})
    });
}

document.getElementById('trip-filter').addEventListener('change', function(){
    let f = this.value;
    document.querySelectorAll('.trip-card').forEach(c=>c.style.display = (f==='all' || c.dataset.status===f) ? '' : 'none');
});

// Maps
document.addEventListener('DOMContentLoaded', ()=>{
    @foreach($trips as $trip)
    @php $cps = $trip->checkpoints->filter(fn($c)=>$c->latitude && $c->longitude); @endphp
    @if($cps->count()>0)
    initMap({{ $trip->id }}, {!! json_encode($cps->map(fn($c)=>['name'=>$c->location,'lat'=>(float)$c->latitude,'lng'=>(float)$c->longitude])->values()) !!});
    @endif
    @endforeach
});

function initMap(id, checkpoints) {
    if(!checkpoints.length) return;
    let map = L.map('map-'+id);
    let lats = checkpoints.map(c=>c.lat), lngs = checkpoints.map(c=>c.lng);
    map.fitBounds([[Math.min(...lats),Math.min(...lngs)],[Math.max(...lats),Math.max(...lngs)]]);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);
    L.polyline(checkpoints.map(c=>[c.lat,c.lng]),{color:'blue',weight:3}).addTo(map);
    checkpoints.forEach((c,i)=>L.marker([c.lat,c.lng]).bindPopup((i+1)+'. '+c.name).addTo(map));
}

document.querySelectorAll('dialog').forEach(d=> d.addEventListener('click', e=>{ if(e.target===d) d.close(); }));

function openEditTempCheckpoint(id, location, address, lat, lng) {
    const modal = document.getElementById('editTempCheckpointModal');
    document.getElementById('edit-temp-location').value = location;
    document.getElementById('edit-temp-address').value = address;
    document.getElementById('edit-temp-lat').value = lat;
    document.getElementById('edit-temp-lng').value = lng;
    document.getElementById('editTempCheckpointForm').action = '/checkpoints/' + id;
    modal.showModal();
}

</script>
@endsection 