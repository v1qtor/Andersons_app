<div>
    @if($visible && $count >= 2)
    <div class="fixed top-4 left-1/2 -translate-x-1/2 z-50 max-w-3xl w-full px-4">
        <div class="rounded-lg shadow-lg bg-yellow-50 border border-yellow-200 p-3 flex items-start gap-3">
            <div class="flex-1">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-yellow-800">{{ $count }} staff unavailable</div>
                    <button wire:click="dismiss" class="text-sm text-yellow-700 hover:underline">Dismiss</button>
                </div>

                <div class="text-sm text-yellow-800 mt-1">
                    @if(count($names))
                        @php
                            $shown = array_slice($names, 0, 5);
                            $more = count($names) > 5;
                            $namesText = implode(', ', $shown) . ($more ? ', and others' : '');
                        @endphp
                        {{ $namesText }} will be unavailable today.
                    @else
                        Staff are unavailable today.
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
