<div class="checkpoint-card bg-gray-50 rounded-xl shadow p-5 flex flex-col justify-between hover:shadow-md transition-shadow duration-200">
    <div>
        <div class="flex items-center gap-2 mb-2">
            <span class="text-xl font-bold text-purple-700">{{ $checkpoint->location }}</span>
            @if($checkpoint->trips->count() > 0)
                <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full">{{ $checkpoint->trips->count() }} trips</span>
            @endif
        </div>
        @if($checkpoint->address)
            <div class="text-gray-700 text-base mb-1">{{ $checkpoint->address }}</div>
        @endif
        @if($checkpoint->description)
            <div class="text-gray-500 text-base mb-2">{{ $checkpoint->description }}</div>
        @endif
        <div class="text-sm text-blue-700">
            By {{ $checkpoint->user ? $checkpoint->user->name : 'System' }}
        </div>
    </div>
    <div class="flex justify-between items-center gap-2 mt-4 pt-3 border-t border-gray-200">
        <button onclick="openEditCheckpoint(
            {{ $checkpoint->id }},
            '{{ addslashes($checkpoint->location) }}',
            '{{ addslashes($checkpoint->description ?? '') }}',
            '{{ addslashes($checkpoint->address ?? '') }}',
            '{{ $checkpoint->latitude ?? '' }}',
            '{{ $checkpoint->longitude ?? '' }}',
            '{{ $checkpoint->folder_id ?? '' }}'
        )" class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-1.5 px-4 rounded text-sm flex-1 transition-colors duration-200">
            Edit
        </button>
        <button onclick="confirmDeleteCheckpoint({{ $checkpoint->id }}, '{{ addslashes($checkpoint->location) }}')" class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-1.5 px-4 rounded text-sm transition-colors duration-200">
            Delete
        </button>
    </div>
</div>

<script>
function confirmDeleteCheckpoint(checkpointId, checkpointName) {
    const confirmed = confirm(
        `Are you sure you want to delete the checkpoint "${checkpointName}"?\n\n` +
        `This action cannot be undone and will:\n` +
        `• Remove it from all associated trips\n` +
        `• Delete it from its folder\n` +
        `• Remove any uploaded images\n\n` +
        `Are you sure you want to continue?`
    );
    
    if (confirmed) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/checkpoints/${checkpointId}`;
        form.style.display = 'none';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>